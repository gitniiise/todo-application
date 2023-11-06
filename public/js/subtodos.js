import { mapPriority } from './todos.js';
import { parseDatetime } from './todos.js';

const addSubtodoForm = document.getElementById('add-subtodo-form');
const subtodoNameInput = document.getElementById('new_subtodo');

addSubtodoForm.addEventListener('submit', async (event) => {
event.preventDefault(); // Verhindere das Standardverhalten des Formulars (Seitenneuladen)
const parentTodoId = document.getElementById('todo-item-name-input').dataset.indexNumber; // Holen Sie die ID der übergeordneten Aufgabe
const newSubtodoName = subtodoNameInput.value; // Holen Sie den Namen der Unteraufgabe
// Hier fügst du die Unteraufgabe hinzu (Kommunikation mit dem Backend)
const data = {
    "new_subtodo": newSubtodoName,
    "parentId": parentTodoId
};
try {
    const response = await fetch('/add_subtodo', {
        method: 'POST',
        body: JSON.stringify({ "new-subtodo-name": newSubtodoName, "parentId": parentTodoId }),
        headers: {
            'Content-Type': 'application/json'
        }
    });
    if (response.status === 200) {
        const result = await response.json();
        const subtodoId = result.subtodoId;
        createSubtodoAsListElement(newSubtodoName, subtodoId)

        const subtodoMessage = document.getElementById('no-subtodos-message');
        if(subtodoMessage){
            subtodoMessage.innerText = '';
        }
    } else {
    console.error('Fehler beim Hinzufügen der Unteraufgabe', response.status);
    }
  } catch (error) {
    console.error('Fehler beim Senden der Anfrage', error.message);
  }
  // Zurücksetzen des Formulars
  subtodoNameInput.value = '';
});



// Diese Funktion zeigt die Unteraufgaben für die übergeordnete Aufgabe mit der gegebenen todoId an.
async function showSubToDos(todoId) {
  // Füge hier den Code ein, um die Unteraufgaben für die gegebene todoId abzurufen und anzuzeigen.

  try {
    // Hier rufst du die API auf, um die Unteraufgaben für die übergeordnete Aufgabe zu erhalten.
    const response = await fetch(`/api/subtodos/${todoId}`, {
      method: 'GET',
    });
    if (response.status === 200) {
        const subtodos = await response.json();
        
        // Hier kannst du den HTML-Code erstellen, um die Unteraufgaben anzuzeigen.
        const subtodoList = document.getElementById('subtodo-list');
        subtodoList.innerHTML = ''; // Leere die vorherigen Unteraufgaben
        console.log(subtodos);
        if (subtodos.length > 0) {
            subtodos.forEach(subtodo => {
                createSubtodoAsListElement(subtodo.name, subtodo.id, subtodo.prio, subtodo.deadline);
        });
        } else {
        // Zeige eine Meldung an, wenn keine Unteraufgaben vorhanden sind.
            const noSubtodosMessage = document.createElement('p');
            noSubtodosMessage.id = 'no-subtodos-message';
            noSubtodosMessage.textContent = 'Keine Unteraufgaben vorhanden.';
            subtodoList.appendChild(noSubtodosMessage);
        }
    } else {
      console.error('Fehler beim Abrufen der Unteraufgaben', response.status);
    }
  } catch (error) {
    console.error('Fehler beim Senden der Anfrage', error.message);
  }
}

// Exportiere die showSubToDos-Funktion, damit sie in todos.js verwendet werden kann
export { showSubToDos };

function createSubtodoAsListElement(subtodoName, subtodoId, subtodoPrio, subtodoDeadline){
    const subtodoItem = document.createElement('li');
    subtodoItem.className = 'subtodo-item';
    
    const subtodoCheckbox = document.createElement('input');
    subtodoCheckbox.type = 'checkbox';
    subtodoCheckbox.className = 'subtodo-checkbox';
    subtodoItem.setAttribute('data-id', subtodoId);
    
    subtodoItem.appendChild(subtodoCheckbox); 
    
    if(subtodoPrio === 1){
        const prioSVG = document.createElement('span');
        prioSVG.className = 'prio-icon';
        prioSVG.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" version="1.0" width="31.000000pt" height="289.000000pt" viewBox="0 0 11.000000 289.000000" preserveAspectRatio="xMidYMid meet" style="width: 10px;"><g transform="translate(0.000000,289.000000) scale(0.100000,-0.100000)" fill="red" stroke="none"><path d="M305 2775 c-52 -18 -102 -73 -111 -121 -4 -21 5 -398 21 -838 29 -870 26 -839 88 -876 41 -25 73 -25 114 0 62 37 59 6 88 876 15 441 25 818 21 837 -18 94 -129 156 -221 122z"></path><path d="M264 521 c-57 -26 -111 -92 -125 -156 -25 -110 45 -228 154 -260 63 -19 71 -19 134 0 100 30 176 147 158 243 -28 150 -185 235 -321 173z"></path></g></svg>';
        subtodoItem.appendChild(prioSVG);
    }
    if(subtodoDeadline != null) {
        const dateString = subtodoDeadline.date;
        const date = new Date(dateString);
        
        // Formatieren Sie das Datum mit dem gewünschten Format
        const formattedDate = new Intl.DateTimeFormat('de-DE', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
        console.log(formattedDate);
        const deadlineVisual = document.createElement('span');
        deadlineVisual.className = 'deadline-info';
        deadlineVisual.innerHTML = formattedDate;
        subtodoItem.appendChild(deadlineVisual);
    }
    subtodoItem.appendChild(document.createTextNode(subtodoName));

    const subtodoList = document.getElementById('subtodo-list');
    subtodoList.appendChild(subtodoItem);
}

const subtodoList = document.getElementById('subtodo-list');
subtodoList.addEventListener('click', async (event) => {
    const target = event.target;
    const clickedLi = target.closest('li.subtodo-item');
    if (target.id === 'save-button') {
        console.log("target save");

        // Der "Speichern"-Button wurde geklickt, entferne das Bearbeitungselement
        const editForm = target.closest('form#update-form-subtodo');
        if (editForm) {
            editForm.remove();
        }
    } else if (target.classList.contains('subtodo-checkbox')) {
        console.log("target checkbox");

        // Checkbox wurde geklickt => delete subtodo
        if (target.checked) {
            console.log("target checkbox true");

            const subtodoId = target.parentElement.dataset.id;

            fetch(`/api/subtodos/${subtodoId}`, {
                method: 'POST',
            })
            .then(response => {
                if (response.status === 204) {
                    //TODO: hieraus dynamsischen Kontent machen
                    const editForm = document.querySelector(`form[data-index-number="${subtodoId}"]`);
                    if(editForm) {
                        editForm.remove();
                    }
                    const subtodoElement = document.querySelector(`li[data-id="${subtodoId}"]`);
                    subtodoElement.style.textDecoration = 'line-through';
                    subtodoElement.classList.add('fade-out');
                    setTimeout(() => {
                        subtodoElement.style.display = 'none';
                    }, 1000); // Ausblenden verzögert
                } else {
                    console.error('Fehler beim Löschen der Unteraufgabe', response.status);
                }
            })
            .catch(error => {
                console.error('Fehler beim Löschen der Unteraufgabe:', error);
            });
        }
    } else if (clickedLi) {
        console.log("target editform");
        // Erstelle ein neues aufklappbares Bearbeitungselement
        const subtodoId = clickedLi.getAttribute('data-id');
        // Überprüfe, ob ein anderes Bearbeitungselement geöffnet ist
        const existingEditForm = subtodoList.querySelector('form#update-form-subtodo');
        
        if (existingEditForm) {
        // Entferne das vorhandene Bearbeitungselement
        existingEditForm.remove();
        }
        const editForm = await createEditForm(subtodoId, clickedLi);
        
        // Füge das Bearbeitungselement nach dem angeklickten <li> hinzu
        await generateEventListenerSaveSubTodo();
    }
});

async function createEditForm(subtodoId, clickedLi) {
    const editForm = document.createElement('form');
    editForm.id = 'update-form-subtodo';
    editForm.dataset.indexNumber = subtodoId;
    console.log('form');
    editForm.classList = 'green-bg';
    const data = await fetchSubTodoDetails(subtodoId);
    const { name, description, prio, deadline } = data;
    //TODO: make dynamik for both entitys
    const actualDescription = description || 'Beschreibung der Unteraufgabe hinzufügen';
    const actualPrio = mapPriority(prio);
    console.log(actualPrio);
    const actualDeadline = deadline ? parseDatetime(deadline.date) : '';

    console.log(data);
    //TODO geht subtodo date and fill in form
    editForm.innerHTML = `
        <div class="form-group">
        <input type="text" id="subtodo-item-name-input" name="name" placeholder="${name}" data-index-number="${subtodoId}" value="${name}">
        </div>
        <div class="form-group">
        <label for="subtodo-item-description">
            <i class="material-icons">Beschreibung:</i>
        </label>
        <textarea id="subtodo-item-description-input" name="description" placeholder="${actualDescription}"></textarea>
        </div>
        <div class="form-group small-group">
        <label for="subtodo-item-priority">
            <i class="material-icons">Priorität:</i>
        </label>
        <select id="subtodo-item-priority-input" name="prio">
            <option value="1">Hoch</option>
            <option value="2">Mittel</option>
            <option value="3">Niedrig</option>
            <option value="4">-</option>
        </select>
        </div>
        <div class="form-group small-group">
        <label for="subtodo-item-deadline">
            <i class="material-icons">Deadline:</i>
        </label>
        <input type="datetime-local" id="subtodo-item-deadline-input" name="deadline">
        </div>
        <button id="save-button-subtodo">Speichern</button>
    `;
    clickedLi.insertAdjacentElement('afterend', editForm);
    document.getElementById('subtodo-item-priority-input').selectedIndex = actualPrio - 1;
    document.getElementById('subtodo-item-deadline-input').value = actualDeadline;
  return editForm;
}

async function fetchSubTodoDetails(subtodoId) {
    try {
        const response = await fetch(`/api/subtodo/${subtodoId}`, {
            method: 'GET',
        });

        if (response.status === 200) {
            const data = await response.json();
            return data;
        } else {
            // Behandlung von Fehlern, wenn die Anfrage an den Server fehlschlägt
            console.error('Fehler beim Abrufen der Unteraufgabendetails', response.status);
            return null;
        }
    } catch (error) {
        console.error('Fehler beim Senden der Abfrage - Unteraufgabe', error.message);
        return null;
    }
}

async function generateEventListenerSaveSubTodo() {
    const saveButtonSubtodo = document.getElementById('save-button-subtodo');
    saveButtonSubtodo.addEventListener('click', async () => {
        //TODO: Dynamisch machen mit Methode in todo entity
        const subtodoId = document.getElementById('subtodo-item-name-input').dataset.indexNumber;
        const updateName = document.getElementById('subtodo-item-name-input').value;
        const updateDescription = document.getElementById('subtodo-item-description-input').value;
        const updatePrio = mapPriority(parseInt(document.getElementById('subtodo-item-priority-input').value));
        const updateDeadline = document.getElementById('subtodo-item-deadline-input').value;

        const subtodoObjectUpdate = JSON.stringify({ updateName, updateDescription, updatePrio, updateDeadline });
        try {
            const response = await fetch(`/api/subtodos/${subtodoId}`, {
                method: 'PUT',
                body: subtodoObjectUpdate,
            });
        } catch (error) {
            console.error('Fehler beim Senden der Anfrage - Unteraufgabe', error.message);
        }
    });
}