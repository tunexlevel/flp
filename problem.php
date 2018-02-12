<?php

//use Problem;

/**
 * Facility Optimization Location Problem
 *
 * @author Opesemowo Babatunde <tunexlevel8@gmail.com>
 */
class problem {
    
    /** 
     * @var MAX_ITERATION Problem the max iteration number 
     */
    const MAX_ITERATION = 1000;
    /** 
     * @var DIMENSION Problem problem dimension 
     */
    const DIMENSIONS = 2;
    /** 
     * @var MAX_VALUE Problem maximum value for x,y 
     */
    const MAX_VALUE = 10;
    /** 
     * @var MAX_VALUE Problem maximum value for x,y 
     */
    const MIN_VALUE = -10;
    /** 
     * @var $PARTICLE_NUMBER  number of parcicles 
     */
    const PARTICLES_NUMBER = 30;
    /** 
     * @var integer <i.> number of potential location 
     */
    const NUM_OF_POTENTIAL_LOC = 3;
    /** 
     * @var integer <j>  number of evacuation center 
     */
    const NUM_OF_EVACUATION_CENTER = 2;
    /** 
     * @var integer <k> number of Local Government  Area 
     */
    const NUM_OF_LGA = 21;
    /** 
     * @var number <Wi> weight of the factor affecting a location 
     */
    public $weight;
    /**
     *  @var boolean <Oij> binary variable 
     */
    public $open;
    /** 
     * @var array <fi> facility cost 
     */
    public $facilityCost;
    /** 
     * @var array <Pik> transportation cost between the potential location and LGA
     */
    public $transCost;
    /** 
     * @var array <dik> the distance between the potential location and LGA 
     */
    public $distance;
    /** 
     * @var boolean <Yik> binary variable where it is 1 if the LGA is served by Potential Location and 0 if not served 
     */
    public $optionalY;
    /** 
     * @var array <Sj> the size of each evacuation center
     */
    public $size;
    /** 
     * @var array <Xk> the quantity of refuse generated by a LGA 
     */
    public $quantity;
    /** 
     * @var integer <Hi> a positive integer value where it is 1 if the 
     * Potential Location is selected and 0 if not selected 
     */
    public $optionalX;
    
    public function __construct(array $param) {
        $this->facilityCost = $param['facilityCost'];
        $this->weight       = $param['weight'];
        $this->distance     = $param['distance'];
        $this->open         = $param['open'];
        $this->optionalY    = $param['optionalY'];
        $this->transCost    = $param['transCost']; 
        $this->quantity     = $param['quantity'];
        $this->optionalX    = $param['optionalX'];
        $this->size         = $param['size']; 
    }
    
    /**
     * fitness function <fitness function>
     * 
     * @return float the function result
     */
    public function objectiveFunction() {
        $minZ = $this->facilityCost() + $this->weightCost() + $this->transportationCost();
        return $minZ;
    }
    
    /**
     * <facilityCost Function> facilityCost()
     * 
     * @return float sum of the facilityCost
     */
    protected function facilityCost() {
        $cost = 0;
        for ($i = 0; $i < Problem::NUM_OF_POTENTIAL_LOC; $i++){
            for ($j = 0; $j < Problem::NUM_OF_EVACUATION_CENTER; $j++){
                $cost += $this->facilityCost[$i] * $this->open[$i][$j];
            }
        }
        return $cost;
    }
    
    /**
     * <weightCost Function> weightCost()
     * 
     * @return float sum of weightCost
     */
    protected function weightCost() {
        $cost = 0;
        for ($i = 0; $i < Problem::NUM_OF_POTENTIAL_LOC; $i++){
            for ($j = 0; $j < Problem::NUM_OF_EVACUATION_CENTER; $j++){
                $cost += $this->weight[$i][$j] * $this->open[$i][$j];
            }
        }
        return $cost;
    }
    
    /**
     * <transportationCost Function> transportationCost()
     * 
     * @return float cost
     */
    protected function transportationCost() {
        $cost = 0;
        for ($i = 0; $i < Problem::NUM_OF_POTENTIAL_LOC; $i++){
            for ($k = 0; $k < Problem::NUM_OF_LGA; $k++){
                $cost += $this->transCost[$i][$k] * $this->distance[$i][$k] * $this->optionalY[$i][$k];
            }
        }
        return $cost;
    }
    
    /**
     * <constraintOne> constraintOne()
     * 
     * it ensures that the demand of each LGA $k is met
     * @param integer $k LGA index
     * @return boolean true or false
     */
    protected function constraintOne($k) {
        $result = 0;
        for ($i = 0; $i < Problem::NUM_OF_POTENTIAL_LOC; $i++){
            $result += $this->optionalY[$i][$k];
        }
        
        if($result === 1){
            return true;
        }
        else{
            return false;
        }
    }
    
    /**
     * <constraintTwo> constraintTwo()
     * 
     * it ensures that the service prepared by a LGA does not exceeds it’s capacity
     * @param integer $i index of the Potential Location
     * @return boolean true or false
     */
    protected function constraintTwo($i) {
        $capacity = 0; $service = 0;
        for ($j = 0; $j < Problem::NUM_OF_EVACUATION_CENTER; $j++){
            $capacity += $this->size[$j] * $this->open[$i][$j];
        }
        
        for ($k = 0; $k < Problem::NUM_OF_LGA; $k++){
            $service += $this->quantity[$k] * $this->optionalY[$i][$k];
        }
        
        if($capacity >= $service){
            return true;
        }
        else{
            return false;
        }
    }
    
    /**
     * <constraintThree> constraintThree()
     * 
     * it ensures that the sum of service by the LGA does not exceeds the capacity of the Evacuation Center
     * @return boolean true or false
     */
    protected function constraintThree() {
        $capacity = 0; $service = 0;
        
        for ($k = 0; $k < Problem::NUM_OF_LGA; $k++){
            $service += $this->quantity[$k];
        }
        
        for ($i = 0; $i < Problem::NUM_OF_POTENTIAL_LOC; $i++){
            for ($j = 0; $j < Problem::NUM_OF_EVACUATION_CENTER; $j++){
                $capacity += $this->size[$j] * $this->open[$i][$j];
            }
        }
        
        if($service <= $capacity){
            return true;
        }
        else{
            return false;
        }
    }
    
    /**
     * <constraintFour> constraintFour()
     * 
     * it ensures that the selected LGAs must used k-size Evacuation Centers
     * @param integer $i index of the Evacuation Center
     * @return boolean true or false
     */
    protected function constraintFour($i) {
        $result = 0;
        for ($j = 0; $j < Problem::NUM_OF_EVACUATION_CENTER; $j++){
            $result += $this->open[$i][$j];
        }
        
        if($result === $this->optionalX[$i]){
            return true;
        }
        else{
            return false;
        }
    }
}