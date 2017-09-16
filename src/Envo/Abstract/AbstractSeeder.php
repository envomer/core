<?php

namespace Envo;

class AbstractSeeder extends \Phinx\Seed\AbstractSeed
{
    public function seed($tableName, $data)
    {
        $table = $this->table($tableName);
        $table->insert($data)
            ->save();
    }

    public function truncateThenSeed($tableName, $data)
    {
        $table = $this->table($tableName);
        $table->truncate();

        $table->insert($data)
            ->save();

        return $table;
    }

}