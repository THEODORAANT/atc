<?php

class Reports
{

	public static function show_table($headings, $data, $caption=false)
	{
		$s = '';

		$s .= '<div class="table-responsive">';
		$s .= '  <table class="table table-hover">';
		if ($caption) {
			$s .= '<caption>'.$caption.'</caption>';
		}
		$s .= '    <thead>';
		$s .= '    	<tr>';
						foreach($headings as $h) {
							$s .= '<th>'.$h.'</th>';
						}
		$s .= '    	</tr>';
		$s .= '    </thead>';
		$s .= '    <tbody>';

			foreach($data as $row) {
				$s .= '<tr>';

					foreach($row as $key => $val) {
						$s .= '<td>'.$val.'</td>';					
					}

				$s .= '</tr>';
			}


		$s .= '    </tbody>';
		$s .= '  </table>';
		$s .= '</div>';

		return $s;
	}

}
