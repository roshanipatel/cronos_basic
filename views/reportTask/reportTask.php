<h1>Report: Actividad</h1>
<?php /* * ********** SEARCH FORM  ****************** */ ?>
<?php
// Required fields
$showManager = Yii::app()->user->hasDirectorPrivileges();
$form = $this->beginWidget('CActiveForm', array(
    //'action' => $actionURL,
    'method' => 'get',
        ));

$aDiaActual = split("/", date("d/m/Y"));
$beginDay = mktime(0,0,0,$aDiaActual[1], 1, $aDiaActual[2]);
$endDay = mktime(0,0,0,$aDiaActual[1] + 1, 0, $aDiaActual[2]);

?>

<table id="tableTaskSearch">
    <tr>
        <td class="title_search_field">Fecha Inicio</td>
        <td class="title_search_field">Fecha Final</td>
        <td class="title_search_field">Personas</td>
        <td class="title_search_field">Días Laborales</td>
        <td class="title_search_field">Festivos</td>
    </tr>
    <tr>
        <td>
            <input type="text" id="open_time" name="open_time" value="<?php echo date("d/m/Y", $beginDay) ?>"/>
        </td>
        <td>
            <input type="text" id="close_time" name="close_time" value="<?php echo date("d/m/Y", $endDay) ?>"/>
        </td>
        <td>
            <input type="text" id="personas" name="personas" />
        </td>
        <td>
            <input type="text" id="diaslaborales" name="diaslaborales" />
        </td>
        <td>
            <input type="text" id="festivos" name="festivos" />
        </td>
    </tr>
    <tr>
        <td colspan="9" align="center">
            <br>
            <script type="text/javascript">
                function projectSearch( frm )
                {
                    frm.action = '';
                    frm.target = '_self';
                    return true;
                }
            </script>
                <script type="text/javascript">
                    function makeReport( frm )
                    {
                        frm.action = '<?php echo $this->createUrl('reportTask/exportActivity'); ?>';
                        frm.target = '_blank';
                        return true;
                    }
                </script>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <?php
    echo CHtml::submitButton('Make report', array(
        'onClick' => 'return makeReport( this.form );',
    ));
    ?>
        </td>
    </tr>
</table>
<script type="text/javascript">
    jQuery(document).ready((function() {
        jQuery( 'input[id^="open_time"],input[id^="close_time"]' )
        .attr('readonly', 'readonly')
        .datepicker(
        {
            'dateFormat': 'dd/mm/yy',
            'timeFormat': 'hh:mm',
            'monthNames': [ 'Enero', 'Febrero', 'Marzo', 'Abril',
                'Mayo', 'Junio', 'Julio', 'Agosto',
                'Setiembre', 'Octubre', 'Noviembre', 'Diciembre' ],
            'showAnim': 'fold',
            'type' : 'date',
            'dayNamesMin' : ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa' ],
            'firstDay' : 1,
            'currentText' : 'Hoy',
            'closeText' : 'Listo',
            'showButtonPanel' : true
        });
        jQuery( "div.ui-datepicker" ).css("font-size", "80%");
    }));
</script>
<?php
$this->endWidget();
?>
