<?
require "vendor/autoload.php";

require('./vendor/yiisoft/yii2/Yii.php');

use superlyons\idGenerator\Snowflake;


$objenv = new \yii\di\Container();

$objenv->set("db",[
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=abc',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

$con = $objenv->get("db");
$cmd = $con->createCommand();


$i=1; 
$static_id = Snowflake::getId(); 
$cmd->insert('snowflake',[ 'id' => $static_id, 'name' => 'Lyons' ])->execute();
function insertdata($param1,$again,$param2) {
	global $i, $static_id, $cmd;
	echo $again === false ? "first" : "again : ".$again['repeat'];

	$id = $i++ < 4 ? $static_id : Snowflake::getId(); 
	echo "\n".$id."\n";
	$cmd->insert('snowflake',[ 'id' => $id, 'name' => 'Lyons'.$i ])->execute();
}

Snowflake::particleHandle("insertdata",[0=>1,2=>3]);

$answer = trim(fgets(STDIN));

?>