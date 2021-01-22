<?php
use PHPUnit\Framework\TestCase;
use piko\Db;
use piko\Piko;

class DbRowTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $db = new Db(['dsn' => 'sqlite::memory:']);
        Piko::set('db', $db);

        $query = <<<EOL
CREATE TABLE contact (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  firstname TEXT,
  lastname TEXT,
  `order` INTEGER
)
EOL;
        $db->exec($query);
    }

    public function testCreate()
    {
        $contact = new Contact();
        $contact->firstname = 'Sylvain';
        $contact->lastname = 'Philip';
        $contact->order = 1; // order is a reserved word

        $contact->on('beforeSave', function($insert) use($contact) {
            $contact->name = $contact->firstname . ' ' . $contact->lastname;
        });

        $contact->save();

        $this->assertEquals(1, $contact->id);
        $this->assertEquals('Sylvain Philip', $contact->name);

    }

    public function testUpdate()
    {
        $contact = new Contact();
        $contact->load(1);

        $this->assertEquals('Sylvain Philip', $contact->name);

        $contact->on('beforeSave', function($insert) use($contact) {
            if (!$insert) {
                $contact->name .= ' updated';
            }
        });

        $contact->save();

        $this->assertEquals(1, $contact->id);
        $this->assertEquals('Sylvain Philip updated', $contact->name);
    }

    public function testDelete()
    {
        $contact = new Contact();

        try {
            $exceptionMsg = '';
            $contact->delete();
        } catch (\RuntimeException $e) {
            $exceptionMsg = $e->getMessage();
        }

        $this->assertEquals('Can\'t delete because item is not loaded.', $exceptionMsg);

        $contact->load(1);

        $contact->delete();

        try {
            $exceptionMsg = '';
            $contact->load(1);
        } catch (\RuntimeException $e) {
            $exceptionMsg = $e->getMessage();
        }

        $this->assertEquals('Error while trying to load item 1', $exceptionMsg);
    }
}

class Contact extends \piko\DbRecord
{
    protected $tableName = 'contact';

    protected $schema = [
        'id'        => self::TYPE_INT,
        'name'      => self::TYPE_STRING,
        'firstname' => self::TYPE_STRING,
        'lastname'  => self::TYPE_STRING,
        'order'     =>  self::TYPE_INT
    ];
}
