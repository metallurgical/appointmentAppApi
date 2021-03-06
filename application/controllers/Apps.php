<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';

class Apps extends REST_Controller
{
	public $url;

	function __construct(){

		parent::__construct();
		$this->load->helper('url');
		$this->url    = current_url();

        /**
         * Set header for cross origin request(CORS)
         * Allow (POST, GET, OPTIONS, PUT, DELETE) method for operations, by default is not available
         * Allow authorization basic authentication
         */
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Method: POST, GET, OPTIONS, PUT, DELETE');
        header("Access-Control-Allow-Headers: X-Custom-Header, X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
       
	}

	/************** THIS FUNCTION WILL CHANGE DEPEND ON COMPLEXITY OF CLIENT SIDE REQUESTED ****************************
	 * @param   [type] [parameter]
	 *          [value][table name]
	 *          eg : crm/type/invoices
	 *          
	 * @param   [key] [parameter]
	 *          [value][table primary key or unique value]
	 *          if use this must be include also the val paremeter
	 *          [val][parameter]
	 *          [value][value for the primary key]
	 *          eg : crm/key/invoice_id/val/42
	 *
	 * 			example Full url : crm/type/invoice/key/invoice_id/val/12
	 * 			
	 * ----------------join table(optional-if dont want to use, no need to include join set paramter)---------
	 * @param   [joinid] [parameter]
	 *          [value][id for the table that need to join]
	 *          eg : joinid/invoice_id @@ if want to add more than one join, separeted by '-' respectively
	 *          if use this must be include also the jointo paremeter and type parameter(mainly for first table to select)
	 *          [jointo][parameter]
	 *          [value][name of the table that need to join]
	 *          ex : jointo/invoice_payments
	 *          eg : if more than one - crm/type/invoices/joinid/invoice_id-customer_id/jointo/invoice_payments-customers          
	 * 
	 **************************************************************************************************/
 

	function dataAll_get() 
    {
        /************** THIS FUNCTION WILL CHANGE DEPEND ON COMPLEXITY OF CLIENT SIDE REQUESTED ****************************
        *
        *
        */
        

        if(($this->get('val') && !$this->get('key')) || ($this->get('key') && !$this->get('val')))
        {
        	$this->response(array('error' => 'The key parameter and value parameter must have'), 400);
        }


        if(($this->get('joinid') && !$this->get('jointo')) || ($this->get('jointo') && !$this->get('joinid')))
        {
        	$this->response(array('error' => 'The joinid parameter and jointo parameter must have'), 400);
        }
        
		
		$type    =  $this->get( 'type' ); // get type of table need to fetch data eg:|customers(user/type/customers)|
		$key     =  $this->get( 'key' );  // UNIQUE ID in table to fetch from eg : |customers(user/type/customers/fetch/all@specified/key/customer_id)
        $val     =  $this->get( 'val' );
		$table   = $type;               // asign type into table variable
		
		
		$join_id = $this->get('joinid');
		$join_id = explode('-', $join_id);
		$join_to = $this->get('jointo');
		$join_to = explode('-', $join_to);

        if ( preg_match('/-/i', $key ) ){

           $arrResultKey = explode( '-', $key );
           $arrResultVal = explode( '-', $val );

            for( $i = 0, $length = count( $arrResultKey ); $i < $length; $i++ ) {
                $where[$arrResultKey[$i]] = $arrResultVal[$i];
            }

        }
        else{

            $where = array( $key => $val );
        }

        
        if ( false !== strpos( $this->url,'joinid' ) ) //if joinid name in url variable exist
        {
        	$value = $this->get('val');

        	if ( false !== strpos( $this->url, 'key' ) )  // return join table with condition applied
        	{
        	 	$where = array($table.".".$key => $value);
        	 	$data[$table] = $this->m->get_data_join($table, $where, $join_to, $join_id, false, false);
        	 	
        	}
            else {
                $data[$table] = $this->m->get_data_join($table,false, $join_to, $join_id, false, false);
            }

        }
        else
        {       

        	 if ( false !== strpos( $this->url,'key' ) ) //if have val string in url - must be include the key parameter also
        	 {
        	 	 
        	 	 $data[$table] = $this->m->get_all_rows( $table,$where, false, false, false, false );

        	 }
        	 else
        	 {

        	 	  $data[$table] = $this->m->get_all_rows( $table,false, false, false, false, false );
        	 }
        	 
        
    }

		/*========================================== RESULTS RESPOND =========================================		
    	 *
    	 * if got the data in query, return respond from the server to the client using exact format
    	 */
        if($data)
        {
            $this->response($data, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'User could not be found'), 404);
        }
        /*========================================= END RESULT ================================================*/
    }


    

    /*================================================*/
    function test_get() 
    {
        
        
        $type = $this->get('type'); 
        $key  = $this->get('key');
        $val  = $this->get('val');

        if ( preg_match('/-/i', $key ) ){

           $arrResultKey = explode( '-', $key );
           $arrResultVal = explode( '-', $val );

            for( $i = 0, $length = count( $arrResultKey ); $i < $length; $i++ ) {
                $where[$arrResultKey[$i]] = $arrResultVal[$i];
            }

        }
        else{

            $where = array( $key => $val );
        }

        $table  = $type; // table

         if ( false !== strpos( $this->url,'key' ) ) // if exist key paramter, we know that they want to use where condition
         {
             
             $data[$table] = $this->m->get_all_rows( $table, $where, false, false, false, false);

         }
         else
         {

              $data[$table] = $this->m->get_all_rows( $table,false, false, false, false, false);
         }
             
      

        
        if( $data )
        {
            $this->response($data, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'User could not be found'), 404);
        }
        
    }
    /*========================================================*/


    public function dataAll_options(){
       
       /**
        * OPTIONS requested set header and status to OK
        * This method majority applied for DELETE and PUT operations
        */
        header( "HTTP/1.1 200 OK" );
        exit();
    }



    public function dataAll_post()
    {
        /************** THIS FUNCTION WILL CHANGE DEPEND ON COMPLEXITY OF CLIENT SIDE REQUESTED ****************************
         * 
         * Multiple or single table inserted
         * Usage(client side) : 
         * For the table : (multiple table inserted)    just seperated with '-' symbol
         *                 (single table inserted)      just write down one name only
         * $formData = array(
                            array('name'=>'emi'),                   -----> data for table customers
                            array('subject_name'=>'akhlak')         -----> data for table subjects
                        );
            $post = array('type'=>'customers-subjects', 'formData'=>$formData); 
         * p/s : Data and table name must be write down in sequence order
         * 
         * Will changes time to time in order to create function for dynamic function
         * PEACE
         */
        
        // get table value from client side
        // get data value from client side
        // set variable loop as a boolean to false for initial start
        // set variable loop as a boolean true value if multiple table detected
        $type      = $this->post('type');
        $arrayData = $this->post('formData'); 
        $loop = false;
        // only for multiple table delete, if have this set to true        
        if (false !== strpos($type,'-')){       // if '-' existed
            $loop = true;                       // set the loop value to TRUE
            $table = explode('-', $type);       // and then explode those '-' into array value
        }
        // if '-' symbol detected
        // count the array size
        // make insert function inside loop
        // loop iteration depend on arraysize
        if($loop == true){            
            $bil = count($table);
            for($i = 0; $i < $bil; $i++){
                $doAdd = $this->m->insert_new_data($arrayData[$i],$table[$i]);                
            }            
                $this->response(array('Respone'=> 'Multiple table Insert into table'), 200);
        }
        
        // if no '-' detected
        // single table inserted function will triggered
        // use the array index 0
        else{
            
            $table = $type;
            if(!isset($arrayData[0])){
                $data_val = $arrayData;
            }
            else{
                $data_val = $arrayData[0];
            }
            
            $doAdd = $this->m->insert_new_data($data_val,$table);
            $this->response( array( 'id' => $doAdd ), 200 );
        }
    
        
          
    }

    public function SendingEmail_get() {

        $idDoc       = $this->get('docId');
        $sesiId      = $this->get('sesiId');
        $patientId   = $this->get('patientId');
        
        $docData     = $this->m->get_specified_row( 'docs', array( 'doc_id' => $idDoc ), false, false, false, false );
        $sesiData    = $this->m->get_specified_row( 'sesi', array( 'sesi_id' => $sesiId ), false, false, false, false );
        $patientData = $this->m->get_specified_row( 'patients', array( 'patient_id' => $patientId ), false, false, false, false ); 

        $msg = 'Hello '.$docData['doc_name'].', there is a new patient who book your session '.$sesiData['sesi_session'].' at '.$sesiData['sesi_date'].'. Patient name is '.$patientData['patient_name'];

        $this->load->helper('email');
        $this->load->library('email');

        $config['protocol']     ='smtp';
        $config['smtp_host']    ='ssl://smtp.googlemail.com';
        $config['smtp_port']    ='465';
        $config['smtp_timeout'] ='30';
        $config['smtp_user']    ='thunderwidedev@gmail.com';
        $config['smtp_pass']    ='thunderwidedev@1234';
        $config['charset']      ='utf-8';
        $config['newline']      ="\r\n";
        $config['wordwrap']     = TRUE;
        $config['mailtype']     = 'text';
        $this->email->initialize($config);

        $this->email->from( 'Appointment Apps' );
        $this->email->to( $docData['doc_email'] ); 
        $this->email->subject('New patient book your session!');
        $this->email->message( $msg );  
      
        // try send mail ant if not able print debug
        if ( ! $this->email->send() ) {

        }

        $this->response( array('success'), 200 );


    }

    public function dataAll_delete()
    {
        /************** THIS FUNCTION WILL CHANGE DEPEND ON COMPLEXITY OF CLIENT SIDE REQUESTED ****************************
         * This function can accept multiple and single table to delete
         * How this can happen? 
         * If want to make multiple delete operation, separate it by '-' :
         * example : type/customer-vendors/key/customer_id-vendor_id/12-30
         * Just separate by '-' symbols to delete multiple table
         * Single table example :
         * example : type/customers/key/customer_id/val/12
         *
         * Multiple table delete notes :
         *     - The 'type','key' and 'val' values must be in order form respectively
         *     - The number of values also must be the same
         *     - If the number of value in 'type' have 3 value, so in 'key' and 'val' also need 3 value otherwise, error will returned
         */
        $loop = false;                          // only for multiple table delete, if have this set to true

        $type  = $this->get('type');            // get type value in url
        if (false !== strpos($type,'-')){       // if '-' existed
            $loop = true;                       // set the loop value to TRUE
            $type  = explode('-', $type);       // and then explode those '-' into array value
        }
        
        $key   = $this->get('key');             // same with type but no need to set loop to true because already set in 'type'
        if (false !== strpos($key,'-')){
            
            $key  = explode('-', $key);
        }
        
        $val   = $this->get('val');             // same with type but no need to set loop to true because already set in 'type'
        if (false !== strpos($val,'-')){
            
            $val  = explode('-', $val);
        }

        // multiple tables delete
        if($loop == true){
            
            $bil = count($type);
            for($i = 0; $i < $bil; $i++){

                $table = $type[$i];
                $where = array($key[$i] => $val[$i]);
                $doDelete = $this->m->delete_data($table, $where);
            }

            $this->response(array('Multiple tables Delete Success'), 200);
        }
        // single table delete
        else{
                $table = $type;
                $where = array($key => $val);
                $doDelete = $this->m->delete_data($table, $where);

            $this->response(array('Single table Delete Success'), 200);
        }
        
    }

    public function dataAll_put(){

        /************** THIS FUNCTION WILL CHANGE DEPEND ON COMPLEXITY OF CLIENT SIDE REQUESTED ****************************    
        *
        */
        $tableToUpdate  = $this->put('type');
        $pk             = $this->put('primaryKey');
        $pkVal          = $this->put('primaryKeyVal');
        
        
        $columnToUpdate = $this->put('formData');
        $usingCondition = array($pk => $pkVal);        
        $kk             = $this->m->update_data($columnToUpdate, $tableToUpdate, $usingCondition);
        $this->response(array('Requestsuccess'), 200);
    }


    public function send_post()
	{
		var_dump($this->request->body);
	}


    function dataInvoice_get() 
    {
        
        
        $data['invoices'] = $this->m->get_invoice_id();

   

        if($data)
        {
            $this->response($data, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'User could not be found'), 404);
        }
    }

    public function dataChart_get(){
        $tahun = $this->get('year');       
        $bulan = array(1 => 'Jan',
                       2 => 'Feb',
                       3 => 'Mac',
                       4 => 'Apr',
                       5 => 'May',
                       6 => 'June',
                       7 => 'July',
                       8 => 'Aug',
                       9 => 'Sep',
                       10 => 'Oct',
                       11 => 'Nov',
                       12 => 'Dec');

        $data = $this->m->get_data_highchart($tahun);
        $month = array();
        $amount = array();
        $amount['name'] = "Amount";
        $textTahun = array();
        $textTahun['tahun'] = $tahun;
       

        foreach($data as $k => $v){

                if(array_key_exists($v['month'],$bulan)){
                    
                    $month['month'][] = $bulan[$v['month']];
                }
        }
               
                
     

        $results = array();
        foreach($data as $key => $value){

            $amount['data'][] = $value['amount'];
        }

        $results = array();       
        array_push($results, $amount); 
        array_push($results, $month);
        array_push($results, $textTahun);
       
        $this->response($results, 200);
    }

}







/*
testinggg
 function test_get() 
    {
        
        
        $type = $this->get('type'); 
        $key  = $this->get('key');
        $val  = $this->get('val');

        if ( preg_match('/-/i', $key ) ){

           $arrResultKey = explode( '-', $key );
           $arrResultVal = explode( '-', $val );

            for( $i = 0, $length = count( $arrResultKey ); $i < $length; $i++ ) {
                $where[$arrResultKey[$i]] = $arrResultVal[$i];
            }

        }
        else{

            $where = array( $key => $val );
        }

        $table  = $type; // table

         if ( false !== strpos( $this->url,'key' ) ) // if exist key paramter, we know that they want to use where condition
         {
             
             $data[$table] = $this->m->get_all_rows( $table, $where, false, false, false, false);

         }
         else
         {

              $data[$table] = $this->m->get_all_rows( $table,false, false, false, false, false);
         }
             
      

        
        if( $data )
        {
            $this->response($data, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'User could not be found'), 404);
        }
        
    }*/