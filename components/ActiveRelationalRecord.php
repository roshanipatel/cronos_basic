<?php
namespace app\components;

use yii\db\ActiveRecord;

class ActiveRelationalRecord extends ActiveRecord
{

    protected function saveRelation( $fieldPivotName, $fieldPivotValue, $fieldVarName, $fieldVarValues, $hasToDeleteBefore = true )
    {
        if( !is_array( $fieldVarValues ) )
        {
            return false;
        }
        // Calling class must be a child
        $clazz = get_called_class();
        assert( is_subclass_of( $clazz, 'ActiveRecord' ) );
        if( $hasToDeleteBefore )
        {
            $clazz::find()->where([$fieldPivotName=>$fieldPivotValue])->delete();
        }
        foreach( $fieldVarValues as $val )
        {
            // Create an instance of the underlying class calling this method
            Yii::trace( 'class: ' . $clazz, 'components.activerelationalrecord' );
            $pc = new $clazz;
            $pc->$fieldPivotName = $fieldPivotValue;
            $pc->$fieldVarName = $val;
            if( !$pc->save() )
                return false;
        }
        return true;
    }
    
    protected function saveRelation2( $fieldPivotName, $fieldPivotValue, $fieldVarName, $fieldVarValues, $fieldAdditional, $fieldValueAdditional, $hasToDeleteBefore = true )
    {
        if( !is_array( $fieldVarValues ) )
        {
            return false;
        }
        // Calling class must be a child
        $clazz = get_called_class();
        assert( is_subclass_of( $clazz, 'ActiveRecord' ) );
        if( $hasToDeleteBefore )
        {
            $clazz::find()->where([$fieldPivotName=>$fieldPivotValue])->delete();
           // $clazz::model()->deleteAll( $fieldPivotName . '=' . $fieldPivotValue );
        }
        foreach( $fieldVarValues as $val )
        {
            // Create an instance of the underlying class calling this method
            Yii::trace( 'class: ' . $clazz, 'components.activerelationalrecord' );
            $pc = new $clazz;
            $pc->$fieldPivotName = $fieldPivotValue;
            $pc->$fieldVarName = $val;
            $pc->$fieldAdditional = $fieldValueAdditional[$val];
            if( !$pc->save() )
                return false;
        }
        return true;
    }

}

?>
