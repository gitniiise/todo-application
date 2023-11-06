<?php
// tests/SubToDoTest.php

use PHPUnit\Framework\TestCase;
use App\Entity\SubToDo;
use App\Entity\Todo;

class SubToDoTest extends TestCase
{
    public function testSubToDoName()
    {
        $subTodo = new SubToDo();
        $subTodo->setName('Test SubTodo');

        $this->assertEquals('Test SubTodo', $subTodo->getName());
    }

    public function testSubToDoDescription()
    {
        $subTodo = new SubToDo();
        $subTodo->setDescription('Description for the subTodo');

        $this->assertEquals('Description for the subTodo', $subTodo->getDescription());
    }

    public function testSubToDoPriority()
    {
        $subTodo = new SubToDo();
        $subTodo->setPrio(1);

        $this->assertEquals(1, $subTodo->getPrio());
    }

    public function testSubToDoParentTodo()
    {
        $subTodo = new SubToDo();
        $parentTodo = new Todo();
        $subTodo->setParentTodo($parentTodo);

        $this->assertEquals($parentTodo, $subTodo->getParentTodo());
    }

    /**
     * Testet die Getter- und Setter-Funktionen für die 'parentTodo'-Beziehung.
     */
    public function testParentTodo()
    {
        $subToDo = new SubToDo();
        $todo = new Todo();

        // Überprüfen, ob der Anfangswert NULL ist
        $this->assertNull($subToDo->getParentTodo());

        // Setter-Funktion verwenden, um die Beziehung zu Todo festzulegen
        $subToDo->setParentTodo($todo);

        // Überprüfen, ob die Beziehung zu Todo korrekt ist
        $this->assertSame($todo, $subToDo->getParentTodo());

        // Setter-Funktion verwenden, um die Beziehung zu Todo zu entfernen
        $subToDo->setParentTodo(null);

        // Überprüfen, ob die Beziehung zu Todo entfernt wurde
        $this->assertNull($subToDo->getParentTodo());
    }

    /**
     * Testet die Validierung der 'name' Eigenschaft.
     */
    public function testNameValidation()
    {
        $subToDo = new SubToDo();
        
        // Teste, ob 'name' nicht leer sein darf
        $subToDo->setName('');
        $this->assertFalse($this->validator->validate($subToDo)->isValid());
        
        // Teste, ob 'name' eine maximale Länge von 255 Zeichen hat
        $subToDo->setName(str_repeat('a', 256));
        $this->assertFalse($this->validator->validate($subToDo)->isValid());
        
        // Teste, ob ein gültiger 'name' bestehen bleibt
        $subToDo->setName('Test SubToDo');
        $this->assertTrue($this->validator->validate($subToDo)->isValid());
    }

    /**
     * Testet die Validierung der 'deadline' Eigenschaft.
     */
    public function testDeadlineValidation()
    {
        $subToDo = new SubToDo();
        
        // Teste, ob 'deadline' ein gültiges DateTime-Objekt sein muss
        $subToDo->setDeadline('invalid_date');
        $this->assertFalse($this->validator->validate($subToDo)->isValid());
        
        // Teste, ob ein gültiges 'deadline'-Datum bestehen bleibt
        $subToDo->setDeadline(new \DateTime());
        $this->assertTrue($this->validator->validate($subToDo)->isValid());
    }
 }
?>