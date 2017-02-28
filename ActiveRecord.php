<?php

/**
 * @author Lyons <[superlyons@163.com]>
 */

namespace superlyons\idGenerator;


use Yii;
use superlyons\idGenerator\Snowflake;

/**
 * 扩展 [[\yii\db\ActiveRecord]] 以支持 PHP Snowflake
 */
class ActiveRecord extends \yii\db\ActiveRecord{
	public $idAttributeTAG="id";
	//workIdentifierTAG = false : mean the workid is randomly generated
    public $workIdentifierTAG="1";
    
    public function insert($runValidation = true, $attributes = null){
		return Snowflake::particleHandle([$this,'insertHandler'],[$runValidation, $attributeNames]);
    }

    public function insertHandler($runValidation = true, $attributeNames = null, $again = null){
        $id=Snowflake::getId($this->workIdentifierTAG);
        if(is_array($again)){
            Yii::warning('Generator id again : '.$again['repeat']." ; New id is : ".$id , __METHOD__);
        }else{
            Yii::info('Generator id is : '.$id, __METHOD__);
        }
        $this->setAttribute($this->idAttributeTAG, $id);
        return parent::insert($runValidation, $attributeNames);
    }    

}
