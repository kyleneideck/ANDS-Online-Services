<?php

/* use static definitions to only load the transform 
 * XSLT once
 */
class Transforms {
	static $qa_transformer = NULL;
	static $qa_level_transformer = NULL;
	static $extrif_to_solr_transformer = NULL;
	static $extrif_to_html_transformer = NULL;
	static $extrif_to_form_transformer = NULL;
	static $feed_to_rif_transformer = NULL;
	static $extrif_to_dc_transformer = NULL;
	static $form_to_cleanrif_transformer = NULL;
	static $clean_ns_transformer = NULL;
	

	static function get_qa_transformer()
	{
		if (is_null(self::$qa_transformer))
		{
			$rmdQualityTest = new DomDocument();
			$rmdQualityTest->load(APP_PATH.'registry_object/transforms/quality_report.xsl');
			$qualityTestproc = new XSLTProcessor();
			$qualityTestproc->importStyleSheet($rmdQualityTest);
			self::$qa_transformer =	$qualityTestproc;
		}

		return self::$qa_transformer;
	}
	
	static function get_qa_level_transformer()
	{
		if (is_null(self::$qa_level_transformer))
		{
			$rmdQualityTest = new DomDocument();
			$rmdQualityTest->load(APP_PATH.'registry_object/transforms/level_report.xsl');
			$qualityTestproc = new XSLTProcessor();
			$qualityTestproc->importStyleSheet($rmdQualityTest);
			self::$qa_level_transformer =	$qualityTestproc;
		}

		return self::$qa_level_transformer;
	}
	
	static function get_extrif_to_solr_transformer()
	{
		if (is_null(self::$extrif_to_solr_transformer))
		{
			$extRifToSOLR = new DomDocument();
			$extRifToSOLR->load(APP_PATH.'registry_object/transforms/extrif_to_solr.xsl');
			$extRifToSOLRproc = new XSLTProcessor();
			$extRifToSOLRproc->importStyleSheet($extRifToSOLR);
			self::$extrif_to_solr_transformer =	$extRifToSOLRproc;
		}

		return self::$extrif_to_solr_transformer;
	}
	
	
	static function get_extrif_to_dc_transformer()
	{
		if (is_null(self::$extrif_to_dc_transformer))
		{
			$extRifToDC = new DomDocument();
			$extRifToDC->load(APP_PATH.'registry_object/transforms/extrif_to_dc.xsl');
			$extRifToDCproc = new XSLTProcessor();
			$extRifToDCproc->importStyleSheet($extRifToDC);
			self::$extrif_to_dc_transformer =	$extRifToDCproc;
		}

		return self::$extrif_to_dc_transformer;
	}
	
	static function get_extrif_to_html_transformer()
	{
		if (is_null(self::$extrif_to_html_transformer))
		{
			$extRifToHtml = new DomDocument();
			$extRifToHtml->load(APP_PATH.'registry_object/transforms/extrif_to_html.xsl');
			$extRifToHtmlproc = new XSLTProcessor();
			$extRifToHtmlproc->importStyleSheet($extRifToHtml);
			self::$extrif_to_html_transformer =	$extRifToHtmlproc;
		}

		return self::$extrif_to_html_transformer;
	}
	
	static function get_extrif_to_form_transformer()
	{
		if (is_null(self::$extrif_to_form_transformer))
		{
			$extRifToForm = new DomDocument();
			$extRifToForm->load(APP_PATH.'registry_object/transforms/extrif_to_new_form.xsl');
			$extRifToFormproc = new XSLTProcessor();
			$extRifToFormproc->importStyleSheet($extRifToForm);
			self::$extrif_to_form_transformer =	$extRifToFormproc;
		}

		return self::$extrif_to_form_transformer;
	}
	
	static function get_feed_to_rif_transformer()
	{
		if (is_null(self::$feed_to_rif_transformer))
		{
			$getRifFromFeed = new DomDocument();
			$getRifFromFeed->load(APP_PATH.'registry_object/transforms/extract_rif_from_feed.xsl');
			$getRifFromFeedproc = new XSLTProcessor();
			$getRifFromFeedproc->importStyleSheet($getRifFromFeed);
			self::$feed_to_rif_transformer =	$getRifFromFeedproc;
		}

		return self::$get_feed_to_rif_transformer;
	}

	static function get_form_to_cleanrif_transformer()
	{
		if (is_null(self::$form_to_cleanrif_transformer))
		{
			$cleanEmtyTags = new DomDocument();
			$cleanEmtyTags->load(APP_PATH.'registry_object/transforms/clean_empty_tags.xsl');
			$cleanEmtyTagsproc = new XSLTProcessor();
			$cleanEmtyTagsproc->importStyleSheet($cleanEmtyTags);
			self::$form_to_cleanrif_transformer =	$cleanEmtyTagsproc;
		}

		return self::$form_to_cleanrif_transformer;
	}


	static function get_clean_ns_transformer()
	{
		if (is_null(self::$clean_ns_transformer))
		{
			$cleanNS = new DomDocument();
			$cleanNS->load(APP_PATH.'registry_object/transforms/clean_ns.xsl');
			$cleanNSproc = new XSLTProcessor();
			$cleanNSproc->importStyleSheet($cleanNS);
			self::$clean_ns_transformer =	$cleanNSproc;
		}

		return self::$clean_ns_transformer;
	}
	
}		