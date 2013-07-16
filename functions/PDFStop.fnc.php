<?php

function PDFStop($handle)
{	//global $htmldocPath,$htmldocAssetsPath;
	global $wkhtmltopdfPath,$wkhtmltopdfAssetsPath,$locale;
	
	$handle['orientation'] = $_SESSION['orientation'];
	unset($_SESSION['orientation']);

	$html_content = ob_get_clean();
	
	//convert to HTML page with CSS		
	$html = '<!DOCTYPE html><HTML lang="'.substr($locale,0,2).'" '.(substr($locale,0,2)=='he' || substr($locale,0,2)=='ar'?' dir="RTL"':'').'><HEAD><meta charset="UTF-8" />';
	if ($handle['css'])
		$html .= '<link rel="stylesheet" type="text/css" href="assets/themes/'.Preferences('THEME').'/stylesheet.css" />';
	$html .= '<TITLE>'.str_replace(_('Print').' ','',ProgramTitle()).'</TITLE></HEAD><BODY>' . $html_content . '</BODY></HTML>';

	//modif Francois: wkhtmltopdf
	if (!empty($wkhtmltopdfPath))
	{		
		if(!empty($wkhtmltopdfAssetsPath))
			$html = str_replace('assets/', $wkhtmltopdfAssetsPath, $html);
		
		require('classes/Wkhtmltopdf.php');
		
		try {
			//indicate to create PDF in the temporary files system directory
			$wkhtmltopdf = new Wkhtmltopdf(array('path' => sys_get_temp_dir()));
			
			$wkhtmltopdf->setBinPath($wkhtmltopdfPath);
			
			if (!empty($handle['orientation']) && $handle['orientation'] == 'landscape')
				$wkhtmltopdf->setOrientation(Wkhtmltopdf::ORIENTATION_LANDSCAPE);
			
			if (!empty($handle['margins']) && is_array($handle['margins']))
				$wkhtmltopdf->setMargins($handle['margins']);
			
			$wkhtmltopdf->setTitle(utf8_decode(str_replace(_('Print').' ','',ProgramTitle())));
			
			//directly pass HTML code
			$wkhtmltopdf->setHtml($html);
			
			//MODE_EMBEDDED displays PDF in browser, MODE_DOWNLOAD forces PDF download
			$wkhtmltopdf->output(Wkhtmltopdf::MODE_EMBEDDED, str_replace(array(_('Print').' ', ' '),array('', '_'),utf8_decode(ProgramTitle())).'.pdf');
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
	else
		echo $html;
}
?>