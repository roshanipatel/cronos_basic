<?php

/**
 * Description of DropBoxColumn
 *
 * @author twocandles
 */
class DropDownColumn extends CGridColumn
{

    public $name;
    public $selected;
    public $selectData;
    public $selectClass;
    /**
     * @var array the HTML options for the data cell tags.
     */
    public $htmlOptions = array( 'class' => 'cell-column-select' );

    public function init()
    {
        // Register script to disable click behaviour of row and center text
        $js = <<<EOD
// Disable click behaviour for selects
jQuery('.cell-column-select')
.live( 'click', function(){
    return false;
});
EOD;
        Yii::app()->getClientScript()->registerScript( __CLASS__ . '#' . $this->id, $js );
    }

    /**
     * Renders the data cell content.
     * This method renders the selectbox
     * @param integer $row the row number (zero-based)
     * @param mixed $data the data associated with the row
     */
    protected function renderDataCellContent( $row, $data )
    {
        $fullClass = 'column-select';
        if( (!empty( $this->selectClass ) ) && ( is_string( $this->selectClass ) ) )
            $fullClass .= " $this->selectClass";
        $selectClass = array(
            'class' => $fullClass,
        );
        echo CHtml::dropDownList(
                $this->evaluateExpression( $this->name, array( 'data' => $data, 'row' => $row ) ), 
                $this->evaluateExpression( $this->selected, array( 'data' => $data, 'row' => $row ) ), 
                $this->evaluateExpression( $this->selectData, array( 'data' => $data, 'row' => $row ) ), 
                $selectClass
        );
    }

}

?>
