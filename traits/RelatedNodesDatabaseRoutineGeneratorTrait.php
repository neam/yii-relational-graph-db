<?php

/**
 * Trait RelatedNodesDatabaseRoutineGeneratorTrait
 *
 * @license BSD-3-Clause
 * @author See https://github.com/neam/yii-relational-graph-db/graphs/contributors
 */
trait RelatedNodesDatabaseRoutineGeneratorTrait
{

    public function relatedNodesRoutines($dryRun, $echo)
    {

        $itemTypes = ItemTypes::where('is_graph_relatable');

        $this->d("Connecting to '" . $this->_db->connectionString . "'\n");

        foreach ($itemTypes as $modelClass => $table) {
            if ($this->_checkTableAndColumnExists($table, "node_id")) {

                $routineName = "remove_node_when_removing_$table";

                $sql = "";
                $sql .= "       AFTER DELETE ON `$table`\n";
                $sql .= "       FOR EACH ROW\n";
                $sql .= "       DELETE FROM node WHERE id = old.node_id;\n";
                $sql .= "       END\n";

                $dropRoutineSql = "DROP TRIGGER IF EXISTS $routineName";

                $createRoutineSql = "CREATE TRIGGER $routineName $sql";

                if ($this->_verbose) {
                    $echo("\n");
                    $echo($dropRoutineSql);
                    $echo($createRoutineSql);
                    $echo("\n");
                }

                if (!$dryRun) {
                    $this->_db->createCommand($dropRoutineSql)->execute();
                    //$this->_db->createCommand("DELIMITER $$")->execute();
                    $this->_db->createCommand($createRoutineSql)->execute();
                    //$this->_db->createCommand("DELIMITER ;")->execute();
                }

            }

        }

    }

}
