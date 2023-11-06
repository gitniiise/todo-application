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

        if (subtodos.length > 0) {
            subtodos.forEach(subtodo => {
                createSubtodoAsListElement(subtodo.name, subtodo.id);
        });
        } else {
        // Zeige eine Meldung an, wenn keine Unteraufgaben vorhanden sind.
            const noSubtodosMessage = document.createElement('p');
            noSubtodosMessage.id = 'no-subtodos-message';
            noSubtodosMessage.textContent = 'Keine Unteraufgaben vorhanden.';
            subtodoList.appendChild(noSubtodosMessage);
        }
    } else {
      console.error('Fehler beim Abrufen der Unteraufgaben', response.status, error.message);
    }
  } catch (error) {
    console.error('Fehler beim Senden der Anfrage', error.message);
  }
}

// Exportiere die showSubToDos-Funktion, damit sie in todos.js verwendet werden kann
export { showSubToDos };

function createSubtodoAsListElement(subtodoName, subtodoId){
    const subtodoItem = document.createElement('li');
    subtodoItem.className = 'subtodo-item';
    
    const subtodoCheckbox = document.createElement('input');
    subtodoCheckbox.type = 'checkbox';
    subtodoCheckbox.className = 'subtodo-checkbox';
    subtodoItem.setAttribute('data-id', subtodoId);
    
    subtodoItem.appendChild(subtodoCheckbox); // Checkbox hinzufügen
    subtodoItem.appendChild(document.createTextNode(subtodoName)); // Text hinzufügen
    
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
        const editForm = target.closest('form#update-form');
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
        const editForm = createEditForm(subtodoId);
        
        // Überprüfe, ob ein anderes Bearbeitungselement geöffnet ist
        const existingEditForm = subtodoList.querySelector('form#update-form');
        
        if (existingEditForm) {
        // Entferne das vorhandene Bearbeitungselement
        existingEditForm.remove();
        }
        
        // Füge das Bearbeitungselement nach dem angeklickten <li> hinzu
        clickedLi.insertAdjacentElement('afterend', editForm);
        generateEventListenerSaveSubTodo();
    }
});

function createEditForm(subtodoId) {
  const editForm = document.createElement('form');
  editForm.id = 'update-form';
  editForm.dataset.indexNumber = subtodoId;
  editForm.classList = 'green-bg';
  //TODO geht subtodo date and fill in form
  editForm.innerHTML = `
    <div class="form-group">
      <input type="text" id="subtodo-item-name-input" name="name" placeholder="Neuer Aufgabenname (optional)" data-index-number="${subtodoId}">
    </div>
    <div class="form-group">
      <label for="subtodo-item-description">
        <i class="material-icons">Beschreibung:</i>
      </label>
      <textarea id="subtodo-item-description-input" name="description" placeholder="Beschreibung der Aufgabe"></textarea>
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
  return editForm;
}

function generateEventListenerSaveSubTodo() {
    const saveButtonSubtodo = document.getElementById('save-button-subtodo');
    saveButtonSubtodo.addEventListener('click', async () => {
        //TODO: Dynamisch machen mit Methode in todo entity
        const subtodoId = document.getElementById('subtodo-item-name-input').dataset.indexNumber;
        const updateName = document.getElementById('subtodo-item-name-input').value;
        const updateDescription = document.getElementById('subtodo-item-description-input').value;
        const updatePrio = mapPriority(parseInt(document.getElementById('subtodo-item-priority-input').value));
        const updateDeadline = document.getElementById('subtodo-item-deadline-input').value;

        const subtodoObjectUpdate = JSON.stringify({ updateName, updateDescription, updatePrio, updateDeadline });
        console.log(subtodoObjectUpdate);
        console.log(subtodoId);
        console.log('Update geht los');
        try {
            const response = await fetch(`/api/subtodos/${subtodoId}`, {
                method: 'PUT',
                body: subtodoObjectUpdate,
            });

            if (response.status === 200) {
                console.log("final");
            } else {
                console.error('Fehler beim Löschen der Unteraufgabe', response.status);
            }
        } catch (error) {
            console.error('Fehler beim Senden der Anfrage - Unteraufgabe', error.message);
        }
    });
}