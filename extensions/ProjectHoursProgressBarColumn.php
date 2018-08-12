<?php

/**
 * Description of DropBoxColumn
 *
 * @author twocandles
 */
class ProjectHoursProgressBarColumn extends CGridColumn {

	public $name;
	public $selected;
	public $selectData;
	public $selectClass;

	/**
	 * @var array the HTML options for the data cell tags.
	 */
	public $htmlOptions = array('class' => 'cell-column-select');

	public function init() {
		// Register script to disable click behaviour of row and center text
		$js = <<<EOD
// Disable click behaviour for selects
jQuery('.cell-column-select')
.live( 'click', function(){
    return false;
});
EOD;
		Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->id, $js);
	}

	/**
	 * Renders the data cell content.
	 * This method renders the selectbox
	 * @param integer $row the row number (zero-based)
	 * @param mixed $data the data associated with the row
	 */
	protected function renderDataCellContent($row, $data) {
		if($data->max_hours == 0) {
			$progressValue = 0;
		} else {
			$progressValue = round($data->getTotalHours() * 100 / $data->max_hours, 2);
		}
		$totalHours = round($data->getTotalHours(), 2);
		$maxHours = round($data->max_hours, 2);
		// Check that progress is less than 100
		$progressValue = min(array($progressValue,100));
		$progressBarId = "progressbar" . $data->id;
		$progressBarParentId = $progressBarId . "parent";
		// To define points of color change
		$thresholds = Yii::app()->params->project_progressbar;
		$p1 = 0;
		if( count($thresholds) != 2 ){
			$p2 = 50;
			$p3 = 70;
		} else {
			$p2 = $thresholds[0];
			$p3 = $thresholds[1];
		}
		echo <<<HTML
<span class="progressBar" id="$progressBarId"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="hours_after_progress_bar"></span>
<script>
	jQuery( "#$progressBarId" ).progressBar($progressValue, {
			max: 100,

			showText: false,
			width: 120,
			height: 12,
			boxImage: 'images/progressbar/progressbar.gif',
			barImage: {
				$p1:  'images/progressbar/progressbg_green.gif',
				$p2: 'images/progressbar/progressbg_yellow.gif',
				$p3: 'images/progressbar/progressbg_red.gif'
			}
		});
/*
	if(window.shownProjects == undefined){
		shownProjects = new Array();
	}
	window.shownProjects.push({
		id: "$progressBarId",
		value: $progressValue,
		total_hours: {$data->getTotalHours()},
		max_hours: {$data->max_hours}
	});*/
</script>
HTML;
	}

}

?>
