import { showSubToDos } from './subtodos.js';

// Übergeordnetes Element (Aufgabenliste), an das die Event-Listener gebunden werden
const todoListContainer = document.getElementById('todo-list');
const todoItems = document.querySelectorAll('.todo-item');
const todoDetails = document.getElementById('todo-item-details');
const todoName = document.getElementById('todo-item-name-input');
const parendItemId = document.getElementById('parent-item-id');
const todoDescription = document.getElementById('todo-item-description-input');
const todoPriority = document.getElementById('todo-item-priority-input');
const todoDeadline = document.getElementById('todo-item-deadline-input');

const popup = document.getElementById('popup');
// Event-Listener für das Klicken auf Aufgaben und Checkboxes (Event Delegation)
todoListContainer.addEventListener('click', async (event) => {
  const target = event.target;
  
  if (target.classList.contains('todo-checkbox')) {
    // Checkbox wurde geklickt
    if (target.checked) {
      event.stopPropagation();
      const todoId = target.parentElement.dataset.id;

      fetch(`/api/todos/${todoId}`, {
        method: 'POST',
      })
        .then(response => {
          if (response.status === 204) {
            const todoElement = document.querySelector(`li[data-id="${todoId}"]`);
            todoElement.style.textDecoration = 'line-through';
            todoElement.classList.add('fade-out');
            setTimeout(() => {
              todoElement.style.display = 'none';
            }, 1000); // Ausblenden verzögert
          } else {
            console.error('Fehler beim Löschen der Aufgabe', response.status);
          }
        })
        .catch(error => {
          console.error('Fehler beim Löschen der Aufgabe:', error);
        });
    }
  } else if (target.classList.contains('todo-item')) {
    // Aufgabe wurde geklickt
    const todoId = target.dataset.id;

    // Hier rufen wir die asynchrone Funktion direkt auf
    (async () => {
      const data = await fetchTodoDetails(todoId);
      if (!data) {
        // Wenn das Abrufen der Aufgabendetails fehlschlägt, breche die Verarbeitung hier ab
        return;
      }

      const { name, description, prio, deadline } = data;
      const actualDescription = description || '';
      const actualPrio = mapPriority(prio);
      const actualDeadline = deadline ? parseDatetime(deadline.date) : '';
      todoName.value = name;
      todoName.dataset.indexNumber = todoId;
      parendItemId.dataset.indexNumber = todoId; // ToDo Id für SubToDo adding
      todoDescription.value = actualDescription;
      todoPriority.selectedIndex = actualPrio - 1;
      todoDeadline.value = actualDeadline;

      // Popup-Fenster und die Aufgaben-Details öffnen
      todoDetails.style.display = 'block';
      popup.style.display = 'block';
      showSubToDos(todoId);
    })();
  }
});

const closeButton = document.getElementById('close-popup');
const saveButton = document.getElementById('save-button');

closeButton.addEventListener('click', () => {
    popup.style.display = 'none';
});

saveButton.addEventListener('click', async () => {
    // Hier kannst du die Änderungen speichern und das Popup schließen
    const todoId = document.getElementById('todo-item-name-input').dataset.indexNumber;
    const updateName = document.getElementById('todo-item-name-input').value;
    const updateDescription = document.getElementById('todo-item-description-input').value;
    const updatePrio = mapPriority(parseInt(document.getElementById('todo-item-priority-input').value));
    const updateDeadline = document.getElementById('todo-item-deadline-input').value;

    const todoObjectUpdate = JSON.stringify({ updateName, updateDescription, updatePrio, updateDeadline });
    try {
        const response = await fetch(`/api/todos/${todoId}`, {
            method: 'PUT',
            body: todoObjectUpdate,
        });

        if (response.status === 200) {
            location.reload();
            setTimeout(() => {
                popup.style.display = 'none';
            }, 1000);
        } else {
            console.error('Fehler beim Löschen der Aufgabe', response.status);
        }
    } catch (error) {
        console.error('Fehler beim Senden der Anfrage', error);
    }
});


async function fetchTodoDetails(todoId) {
    try {
        const response = await fetch(`/api/todos/${todoId}`, {
            method: 'GET',
        });

        if (response.status === 200) {
            const data = await response.json();
            return data;
        } else {
            // Behandlung von Fehlern, wenn die Anfrage an den Server fehlschlägt
            console.error('Fehler beim Abrufen der Aufgabendetails', response.status);
            return null;
        }
    } catch (error) {
        console.error(error);
        return null;
    }
}

// Prioritäts-Mapping-Funktion
function mapPriority(priority) {
    switch (priority) {
        case null:
            return 4;
        case 1:
            return 1;
        case 2:
            return 2;
        case 3:
            return 3;
        default:
            return 4;
    }
}
export { mapPriority };


/**
 * Parst einen Datumszeit-String in das gewünschte Format "yyyy-MM-ddThh:mm".
 * @param {string} datetimeString - Der zu parsende Datumszeit-String.
 * @returns {string} Der geparste Datumszeit-String im gewünschten Format.
 * @throws {Error} Wenn der Datumszeit-String null, leer ist oder das Format ungültig ist.
 */
function parseDatetime(datetimeString) {
    // Erstelle ein JavaScript Date-Objekt aus dem angegebenen String
    const date = new Date(datetimeString);

    if (isNaN(date)) {
        throw new Error('Invalid datetime string');
    }

    // Formatieren des Date-Objekts im gewünschten Format "yyyy-MM-ddThh:mm"
    const formattedDatetime = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getDate().toString().padStart(2, '0')}T${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`;

    return formattedDatetime;
}
export { parseDatetime };


// Verwende diese globalen Variablen für die Sortierung
let sortType = 'name'; // Standard-Sortiertyp
let sortOrder = 'asc'; // Standard-Sortierreihenfolge

// Finde die Sortierbuttons und füge Event-Listener hinzu
const sortButtons = document.querySelectorAll('button[data-sort]');

sortButtons.forEach((button) => {
  button.addEventListener('click', async () => {
    sortType = button.dataset.sort;
    sortOrder = button.dataset.order === 'asc' ? 'desc' : 'asc';
    console.log(button.dataset.order);
    console.log(sortOrder);

    // Ändere das Text-Label des Buttons basierend auf der Sortierreihenfolge
    button.getElementsByTagName("div")[0].innerText = `${sortOrder === 'asc' ? '↑' : '↓'}`;

    const sortedTodoList = await sortTodoList(sortType, sortOrder);
    button.dataset.order = sortOrder;
    const todoListContainer = document.getElementById('todo-list');
    todoListContainer.innerHTML = ''; // Löschen Sie die vorhandenen Aufgaben in der Liste

    sortedTodoList.forEach((todo) => {
        const todoItem = document.createElement('li');
        todoItem.className = 'todo-item';
        todoItem.setAttribute('data-id', todo.id);
        const formattedDeadline = new Intl.DateTimeFormat('de-DE', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }).format(new Date(todo.deadline));

        // Fügen Sie hier den Aufgabeninhalt ein, ähnlich wie in Ihrem ursprünglichen Template
        todoItem.innerHTML = `
        <input type="checkbox" class="todo-checkbox">
        ${todo.prio === 1 ? '<span class="prio-icon"><svg xmlns="http://www.w3.org/2000/svg" version="1.0" width="31.000000pt" height="289.000000pt" viewBox="0 0 11.000000 289.000000" preserveAspectRatio="xMidYMid meet" style="width: 10px;"><g transform="translate(0.000000,289.000000) scale(0.100000,-0.100000)" fill="red" stroke="none"><path d="M305 2775 c-52 -18 -102 -73 -111 -121 -4 -21 5 -398 21 -838 29 -870 26 -839 88 -876 41 -25 73 -25 114 0 62 37 59 6 88 876 15 441 25 818 21 837 -18 94 -129 156 -221 122z"></path><path d="M264 521 c-57 -26 -111 -92 -125 -156 -25 -110 45 -228 154 -260 63 -19 71 -19 134 0 100 30 176 147 158 243 -28 150 -185 235 -321 173z"></path></g></svg></span>' : ''}
        ${todo.name}
        ${todo.deadline !== null ? `<span class="deadline-info">${formattedDeadline}</span>` : ''}`;

        todoListContainer.appendChild(todoItem);
    });
  });
});

// Sortierfunktion für die Aufgabenliste
async function sortTodoList(sortType, sortOrder) {
    try {
        const response = await fetch('/api/todos', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
        });
        if (response.status === 200) {
            const todos = await response.json();
            var sortedTodos = todos.sort((a, b) => {
                if (sortType === 'prio') {
                    return sortOrder === 'asc' ? mapPriority(a.prio) - mapPriority(b.prio) : mapPriority(b.prio) - mapPriority(a.prio);
                } else if (sortType === 'deadline') {
                // Annahme: deadline ist ein Date-Objekt
                return sortOrder === 'asc' ? new Date(a.deadline) - new Date(b.deadline) : new Date(b.deadline) - new Date(a.deadline);
                }
                return 0; // Standard, keine Änderung in der Reihenfolge
            });
            } else {
            console.error('Fehler beim Abrufen der Aufgaben', response.status);
        }
    } catch (error) {
        console.error('Fehler beim Senden der Anfrage', error);
    }
    return sortedTodos;
}