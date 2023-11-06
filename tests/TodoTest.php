<?php 
// tests/TodoTest.php

use PHPUnit\Framework\TestCase;
use App\Entity\Todo;

class TodoTest extends TestCase
{
    public function testTodoName()
    {
        $todo = new Todo();
        $todo->setName('Test Todo');

        $this->assertEquals('Test Todo', $todo->getName());
    }

    public function testTodoDescription()
    {
        $todo = new Todo();
        $todo->setDescription('Description for the todo');

        $this->assertEquals('Description for the todo', $todo->getDescription());
    }

    public function testTodoPriority()
    {
        $todo = new Todo();
        $todo->setPrio(2);

        $this->assertEquals(2, $todo->getPrio());
    }

    public function testTodoDeadline()
    {
        $todo = new Todo();
        $deadline = new \DateTime('2023-12-31');
        $todo->setDeadline($deadline);

        $this->assertEquals($deadline, $todo->getDeadline());
    }

    /**
     * Testet die Getter- und Setter-Funktionen für die 'subToDos'-Beziehung.
     */
    public function testSubToDos()
    {
        $todo = new Todo();
        $subToDo1 = new SubToDo();
        $subToDo2 = new SubToDo();

        // Überprüfen, ob die Anfangswerte leere Collection ist
        $this->assertCount(0, $todo->getSubToDos());

        // Setter-Funktion verwenden, um SubTodos hinzuzufügen
        $todo->addSubToDo($subToDo1);
        $todo->addSubToDo($subToDo2);

        // Überprüfen, ob die SubTodos hinzugefügt wurden
        $this->assertCount(2, $todo->getSubToDos());

        // Überprüfen, ob die Beziehung zu Todo in den SubTodos gesetzt ist
        $this->assertSame($todo, $subToDo1->getParentTodo());
        $this->assertSame($todo, $subToDo2->getParentTodo());

        // Setter-Funktion verwenden, um SubTodos zu entfernen
        $todo->removeSubToDo($subToDo1);

        // Überprüfen, ob ein SubTodo entfernt wurde
        $this->assertCount(1, $todo->getSubToDos());

        // Überprüfen, ob die Beziehung zu Todo in den verbleibenden SubTodos korrekt ist
        $this->assertNull($subToDo1->getParentTodo());
        $this->assertSame($todo, $subToDo2->getParentTodo());
    }

    /**
     * Testet die Validierung der 'name' Eigenschaft.
     */
    public function testNameValidation()
    {
        $todo = new Todo();
        
        // Teste, ob 'name' nicht leer sein darf
        $todo->setName('');
        $this->assertFalse($this->validator->validate($todo)->isValid());
        
        // Teste, ob 'name' eine maximale Länge von 255 Zeichen hat
        $todo->setName(str_repeat('a', 256));
        $this->assertFalse($this->validator->validate($todo)->isValid());
        
        // Teste, ob ein gültiger 'name' bestehen bleibt
        $todo->setName('Test Todo');
        $this->assertTrue($this->validator->validate($todo)->isValid());
    }

    /**
     * Testet die Validierung der 'deadline' Eigenschaft.
     */
    public function testDeadlineValidation()
    {
        $todo = new Todo();
        
        // Teste, ob 'deadline' ein gültiges DateTime-Objekt sein muss
        $todo->setDeadline('invalid_date');
        $this->assertFalse($this->validator->validate($todo)->isValid());
        
        // Teste, ob ein gültiges 'deadline'-Datum bestehen bleibt
        $todo->setDeadline(new \DateTime());
        $this->assertTrue($this->validator->validate($todo)->isValid());
    }
}
?>