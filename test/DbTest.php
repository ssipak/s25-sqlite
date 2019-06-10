<?php

use PHPUnit\Framework\TestCase;
use S25\SQLite\Db;

final class DbTest extends TestCase
{
  protected function setupDb(): Db
  {
    $tempDbFile = tempnam(sys_get_temp_dir(), 's25-sqlite-test_');
    return new Db($tempDbFile, SQLITE3_OPEN_CREATE|SQLITE3_OPEN_READWRITE);
  }

  public function testEscapingOfIdentifiers()
  {
    $db = $this->setupDb();

    $sql = <<<SQL
DROP TABLE IF EXISTS "esc""test";
CREATE TABLE "esc""test" (
  "column name containing "" etc" TEXT(1),
  "insert" TEXT(2)
);
SQL;
    $db->exec($sql);
    $table = $db->table('esc"test');
    $table->insert(['column name containing " etc' => 'a', 'insert' => 'b']);
    $this->assertEquals(
      [['column name containing " etc' => 'a', 'insert' => 'b']],
      $table->select('*', ['insert' => 'b'])->rows(SQLITE3_ASSOC)
    );
  }

  public function testScenario()
  {
    $db = $this->setupDb();
    $db->woFkCheck(function(Db $db) {
      $db->exec(file_get_contents(__DIR__.'/data/schema.sql'));
    });
    $db->exec(file_get_contents(__DIR__.'/data/data.sql'));

    $faith = $db->table('faith');
    $person = $db->table('person');
    $wedlock = $db->table('wedlock');

    $yazychestvoId = $faith->select(['id'], ['name' => 'Язычество'])->value();
    $pravoslavieId = $faith->select(['id'], ['name' => 'Православие'])->value();
    $rurikId = $person->select(['id'], ['father_id' => null, 'name' => 'Рюрик'])->value();

    // Generation 2

    $olgaId = $person->insert([
      'name' => 'Ольга',
      'gender' => 'F',
      'faith_id' => $pravoslavieId,
    ]);
    $igorId = $person->insert([
      'father_id' => $rurikId,
      'name' => 'Игорь',
      'is_alive' => false,
      'faith_id' => $yazychestvoId,
    ]);

    $wedlock->insert([
      'husband_id' => $igorId,
      'wife_id' => $olgaId
    ]);

    // Generation 3

    $svyatoslavId = $person->insert([
      'father_id' => $igorId,
      'mother_id' => $olgaId,
      'name' => 'Святослав',
      'faith_id' => $yazychestvoId,
    ]);

    // Generation 4

    $person->insert([
      'father_id' => $svyatoslavId,
      'name' => 'Ярополк',
    ]);
    $olegId = $person->insert([
      'father_id' => $svyatoslavId,
      'name' => 'Алег',
    ]);
    $person->insert([
      'father_id' => $svyatoslavId,
      'name' => 'Владимир',
      'faith_id' => $yazychestvoId,
    ]);

    $this->assertEquals('Алег', $person->select(['name'], ['id' => $olegId])->value());
    $this->assertEquals(1, $person->update(['name' => 'Олег'], ['name' => 'Алег']));
    $this->assertEquals('Олег', $person->select(['name'], ['id' => $olegId])->value());
    $this->assertEquals(10, count($person->select(['id'])->rows()));
  }
}