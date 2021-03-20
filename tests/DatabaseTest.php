<?php

require_once __DIR__."/../Database.php";

use PHPUnit\Framework\TestCase;


class DatabaseTest extends TestCase
{
    private Database $dut;
    private array $expected_records;

    protected function setUp(): void 
    {
        $this->expected_records = [
            (object)['index'=>1, 'name'=>'apple', 'weight'=>200.03, 'price'=>40.05],
            (object)['index'=>2, 'name'=>'banana','weight'=>400.03, 'price'=>80.05],
            (object)['index'=>3, 'name'=>'orange','weight'=>193.57, 'price'=>45.75],
            (object)['index'=>4, 'name'=>'grape', 'weight'=>393.43, 'price'=>100.28],
        ];
        $this->dut = Database::get();
        $this->dut->run('insert into `fruits` (`name`, `weight`, `price`) values'
            ." ('apple', 200.03, 40.05), ('banana', 400.03, 80.05),"
            ." ('orange', 193.57, 45.75), ('grape', 393.43, 100.28)")->current();
    }

    protected function tearDown() : void 
    {
        unset($this->expected_records);
        $this->dut->run('truncate table `fruits`')->current();
    }

    function testConnection() 
    {        
        $records = $this->dut->run('select :number', [':number'=>1]);
        $this->assertEquals(1,current($records->current()));
    }

    function testSelect() 
    {
        $records = $this->dut->run('select * from `fruits`');
        $index = 0;
        foreach($records as $record) 
        {
            $this->assertEquals($this->expected_records[$index++], (object)$record);
        }
    }

    function testInsert() 
    {
        $this->expected_records[4] = new StdClass();
        $this->expected_records[4]->index = 5;
        $this->expected_records[4]->name = 'watermelon';
        $this->expected_records[4]->weight = 555.55;
        $this->expected_records[4]->price = 70.00;
        $this->dut->run("insert into `fruits` (`name`, `weight`, `price`) values (:name, :weight, :price)",
            [':name'=>'watermelon', ':weight'=>555.55, ':price'=>70.00])->current();
        $this->testSelect();
     }

    function testUpdate() 
    {
        $this->expected_records[1-1]->name = 'Mandarin';
        $this->dut->run('update `fruits` set `name`= :name where `index`= :index', [':name'=>'Mandarin', ':index'=>1])->current();
        $this->testSelect();
    }

    function testDelete() 
    {
        unset($this->expected_records[3-1]);
        $this->expected_records = array_values($this->expected_records);
        $this->dut->run('delete from `fruits` where `name`= :name', [':name'=>'orange'])->current();
        $this->testSelect();
    }
    
    function testTransaction()
    {       
        $first_expected_records = [
            ['index'=>1, 'name'=>'Pingguo',     'weight'=>200.03, 'price'=>40.05],
            ['index'=>2, 'name'=>'banana',      'weight'=>400.03, 'price'=>60.50],
            ['index'=>3, 'name'=>'orange',      'weight'=>193.57, 'price'=>45.75],
            ['index'=>5, 'name'=>'watermelon',  'weight'=>777.77, 'price'=>88.88],
        ];
        
        $first_queries= [   
            [   'statement'=>'update `fruits` set `name`= :name where `index`= :index', 
                'parameters'=>[':name'=>'Pingguo', ':index'=>1]     ],
            [   'statement'=>'update `fruits` set `price`= :price where `name`= :name',
                'parameters'=>[':price'=>60.50, ':name'=>'banana']  ],
            [   'statement'=>'delete from `fruits` where `name`= :name',
                'parameters'=>[':name'=>'grape']  ],
            [   'statement'=>'insert into `fruits` (`name`, `weight`, `price`) values (:name, :weight, :price)',
                'parameters'=>[':name'=>'watermelon', ':weight'=>777.77, ':price'=>88.88]  ],
            [   'statement'=>'select * from `fruits`',
                'parameters'=>[] ]            
        ];
        $first_outputs = $this->dut->transact($first_queries);
        $this->assertEquals(1, count($first_outputs));
        $index = 0;
        foreach($first_outputs[0] as $record)
        {
            $this->assertEquals($first_expected_records[$index++], $record);
        }
        $this->assertEquals(4, $index);
                    
        $second_expected_records = [
            ['index'=>1, 'name'=>'apple', 'weight'=>200.03, 'price'=>40.05],
            ['index'=>2, 'name'=>'banana','weight'=>400.03, 'price'=>80.05],
            ['index'=>3, 'name'=>'orange','weight'=>193.57, 'price'=>45.75],
            ['index'=>6, 'name'=>'grape', 'weight'=>393.43, 'price'=>100.28],
        ];        
        $second_queries =    [   
            [   'statement'=>'delete from `fruits` where `name`= :name',
                'parameters'=>[':name'=>'watermelon']  ],
            [   'statement'=>'update `fruits` set `name`= :name where `index`= :index',
                'parameters'=>[':name'=>'apple', ':index'=>1]     ],
            [   'statement'=>'insert into `fruits` (`name`, `weight`, `price`) values (:name, :weight, :price)',
                'parameters'=>[':name'=>'grape', ':weight'=>393.43, ':price'=>100.28]  ],
            [   'statement'=>'update `fruits` set `price`= :price where `name`= :name',
                'parameters'=>[':price'=>80.05, ':name'=>'banana']  ],
            [   'statement'=>'select * from `fruits` where price between :lower and :upper',
                'parameters'=>[':lower'=>40, ':upper'=>90] ],
        ];        
        $second_outputs = $this->dut->transact($second_queries);
        $this->assertEquals(1, count($second_outputs));
        $index = 0;
        foreach($second_outputs[0] as $record)
        {
            $this->assertEquals($second_expected_records[$index++], $record);
        }
        $this->assertEquals(3, $index);       
    }
}
