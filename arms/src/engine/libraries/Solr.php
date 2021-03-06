<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * SOLR class for use globally
 * Search functionality
 * Index functionality
 * @author : <minh.nguyen@ands.org.au>
 */
class Solr {

	private $CI;
	private $solr_url;
	private $result;
	private $options;
    private $multi_valued_fields;

	/**
	 * Construction of this class
	 */
	function __construct(){
        $this->CI =& get_instance();
		$this->CI->load->library('session');
		$this->init();
    }

    /**
     * Initialize the solr class ready for call
     * @return [type] [description]
     */
    function init(){
    	$this->solr_url = $this->CI->config->item('solr_url');
    	$this->options = array('q'=>'*:*','start'=>'0','indent'=>'on', 'wt'=>'json', 'fl'=>'*', 'rows'=>'10');
        $this->multi_valued_fields = array('facet.field', 'fq');
    	return true;
    }

    /**
     * Manually set the option for solr search
     * @param string $field 
     * @param string $value
     */
    function setOpt($field, $value){
        if(isset($this->options[$field])){
            if(is_array($this->options[$field])){
                array_push($this->options[$field], $value);
            }else{
                if(in_array($field, $this->multi_valued_fields)){
                    $this->options[$field] = array($this->options[$field], $value);
                }else{
                    $this->options[$field] = $value;
                }
            }
        }else{
    	   $this->options[$field] = $value;
        }
    }

   /**
     * Manually unsset the option for solr search
     * @param string $field 
     * @param string $value
     */
    function clearOpt($field){
        if(isset($this->options[$field])){          
           unset($this->options[$field]);
        }
    }

    /**
     * get the existing option
     * @param  string $field 
     * @return value 
     */
    function getOpt($field){
        if(isset($this->options[$field])){
            return $this->options[$field];
        }else return null;
    }

    /**
     * Return all of the options, mainly for debugging
     * @return array of SOLR options
     */
    public function getOptions(){
        return $this->options;
    }

     /**
     * Manually set the facet option for solr search (and enable the facet functionality)
     * @param string $field 
     * @param string $value
     */
    function setFacetOpt($field, $value=null){
        $this->setOpt('facet','true');
        $this->setOpt('facet.' . $field, $value); 
    }


    /**
     * Manually set the solr url
     * @param string $value http link for solr url, defaults to be the value in the config
     */
    function setSolrUrl($value){
    	$this->solr_url = $value;
    }


    /**
     * return the total numFound of the search result
     * @return integer
     */
    function getNumFound(){
    	return (int) $this->result->{'response'}->{'numFound'};
    }

    /**
     * get SOLR result header
     * @return array 
     */
    function getHeader(){
    	return $this->result->{'responseHeader'};
    }

    /**
     * get SOLR result response
     * @return array 
     */
    function getResult(){
    	return $this->result->{'response'};
    }

    function getFacet(){
        return $this->result->{'facet_counts'};
    }

    /**
     * get SOLR facet query response by field name
     * @param  string $facet_field the name of a facet field (earlier instantiated with setOpt())
     * @return array 
     */
    function getFacetResult($facet_field){
        if (isset($this->result->facet_counts->facet_fields->{$facet_field}))
        {
            // Sort the pairs (they arrive in list form, we want them as value=>count tuples)
            $value_pair_list = $this->result->facet_counts->facet_fields->{$facet_field};
            $tuples = array();
            for ($i=0; $i<count($value_pair_list)-1; $i+=2)
            {
                $tuples[$value_pair_list[$i]] = $value_pair_list[$i+1];
            }
           // echo $facet_field;
          //  print_r($tuples);
            return $tuples;
        }
        else
        {
            return array();
        }
    }
    
    /**
     * Sample simple search
     * @param  string $term a full text search on this term
     * @return array
     */
	function search($term){
		$this->options['q']='+fulltext:'.$term;
		return $this->executeSearch();
	}

    /**
     * Add query condition
     * @param  string $condition add a query condition to this request (appends to q=)
     */
    function addQueryCondition($condition){
        $this->options['q'].=' '. $condition;
    }

    /**
     * Construct a field string based on SOLR OPTIONS, for posting
     * @return string fields_string
     */
    function constructFieldString(){
        $fields_string='';
        foreach($this->options as $key=>$value) {
            if(is_array($value)){
                foreach($value as $v){
                   $fields_string .= $key.'='.rawurlencode($v).'&';
                }
            }else{
                $fields_string .= $key.'='.rawurlencode($value).'&';
            }
        }//build the string
        return $fields_string;
    }

	/**
	 * Execute the search based on the given options
	 * @return array results
	 */
	function executeSearch($as_array = false){
        $content = $this->post($this->constructFieldString(), 'select');
		$json = json_decode($content, $as_array);
		if($json){
			$this->result = $json;
			return $this->result;
		}else{
			throw new Exception('SOLR Query failed....ERROR:'.$content.'<br/> QUERY: '.$this->constructFieldString());
		}
	}

    /**
     * Post a set of documents to SOLR
     * @param  string of xml $docs   
     * @param  string $handle [select|update]
     * @return json|xml return values
     */
    function post($docs, $handle='select'){
        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL,$this->solr_url.$handle);//post to SOLR
        //curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
        curl_setopt($ch,CURLOPT_POSTFIELDS,$docs);//post the field strings
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
        $content = curl_exec($ch);//execute the curl
        curl_close($ch);//close the curl
        return $content;
    }

    function addDoc($docs){
        return curl_post($this->solr_url.'update?wt=json', $docs);
    }

    function commit(){
        return curl_post($this->solr_url.'update?wt=json&commit=true', '<commit waitSearcher="false"/>');
    }


    function deleteByQueryCondition($query_condition)
    {
        if ($query_condition)
        {
            $result = curl_post($this->solr_url.'update?commit=true&wt=json',
                                     '<delete><query>'.$query_condition.'</query></delete>');    
            return $result;
        }
    }

    function deleteByID($id){
        if($id){
             $result = $this->deleteByQueryCondition('id:'.$id);
        }
    }


    function clear($data_source_id='all'){
        if($data_source_id!='all'){
            $query = 'data_source_id:("'.$data_source_id.'")';
        }else{
            $query = '*:*';
        }

        $result = curl_post($this->solr_url.'update?commit=true&wt=json', '<delete><query>'.$query.'</query></delete>');    
        //$result .= curl_post($this->solr_url.'update?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
        return $result; 
        //return curl_post($this->solr_url.'update?wt=json&commit=true', '<delete>'.$query.'</delete>');
    }
}