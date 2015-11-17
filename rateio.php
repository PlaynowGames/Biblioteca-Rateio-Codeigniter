<?php
/* 
SISTEMA DE RATEIO 
Desenvolvido por Erick Eden Fróes

usage:
$this->rateio->get();
*/



if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Rateio {
	
	private $_ci;
	private $totalVagas;
	public  $procedimento_id;
	public  $tipoprocedimento_id;

 	function __construct()
    {
    	
    	$this->_ci =& get_instance();
		
	}
	
	private function getVagas(){

		$prestadores = $this->whichPrestador();
		
		$query = $this->_ci->db->query("SELECT * FROM prestadores_vagas WHERE prestador_id IN ($prestadores) order by prestador_id ASC");
		$linha = $query->result_array();
		
	    $this->totalVagas = count($linha);
		foreach($linha as $l){
		 $vagas[$l[prestador_id]][] = array("data"=>$l['data'],"vaga_id"=>$l['id'],"status"=>$l['status']);  	
		}
		return $vagas; 
	}
	
	 public function whichPrestador(){

	    $query = $this->_ci->db->query("SELECT prestador_id FROM prestadores_tipoprocedimento 
		WHERE 
		procedimento_id='".$this->procedimento_id."' and 
		tipoprocedimento_id='".$this->tipoprocedimento_id."'");
		$linha = $query->result_array();
		
		foreach($linha as $l){
		 $ps .= "'".$l['prestador_id']."',";  	
		}
		
		return substr($ps,0,-1);
			
	 }
	
	
	private function getPrestadores(){
	    $prestadores = $this->whichPrestador();
		$query = $this->_ci->db->query("SELECT * FROM prestadores_vagas WHERE prestador_id IN ($prestadores) group by prestador_id order by prestador_id ASC");
		$linha = $query->result_array();
		
        foreach($linha as $p){
	         $prestadores[] = $p['prestador_id'];  
        }
		
		for($i=0; $i<=$this->totalVagas; $i++){
	      $vs[] = $prestadores;
	    }
	
	   return $vs;
	}
	
	private function make(){
	 
	 $roundIndex = -1;
	 $vagas = $this->getVagas();
	 $prestadores = $this->getPrestadores();
	 
	 while(sizeof($vagas) > 0){
         $roundIndex++;
	     $keys = $prestadores[$roundIndex];
	
	     for($b=0; $b<sizeof($keys); $b++){
             $key = $prestadores[$roundIndex][$b];
	         $vaga = array_shift($vagas[$key]);
	         if($vaga == ""){
	            continue;
	         }
			 
			 if($vaga['status'] == 1){
	            continue;
	         }
			 
            $nova[] = array("$key",$vaga);
	     }
	    
		 if($roundIndex >= $this->totalVagas){
	       break;
		 }
	 }

	 return $nova;
	}
	
	public function get(){
		$vagas = $this->make();
		return array_shift($vagas);
	} 

}