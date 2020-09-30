<?php

namespace App\Library;

class Player
{
    public $results    = [];
    public $diceGiving = 0;
    
    public $additionalDice = 0;
    public $dice           = 0;
    public $point          = 0;
    
    public function __construct($dice){
        $this->dice = $dice;
    }
    
    public function done(){
        $this->results        = [];
        $this->dice           = 0;
        $this->additionalDice = 0;
        $this->diceGiving     = 0;
    }
    
    public function addDice($value){
        $this->dice += $value;
        $this->additionalDice = $value;
    }

    public function rollAllTheDice(){
        $this->preparation();
        
        $n = 1;
        $dice = $this->dice;
        // if($dice == 0) $this->results = [99]
        while($n <= $dice){
            $result = $this->rollTheDice();
            $this->results[] = $result;
            $this->evaluation($result);
            $n++;
        }
    }

    private function rollTheDice(){
        return collect([1, 2, 3, 4, 5, 6])->random();
    }

    private function evaluation($result){
        if($result == 1){
            $this->diceGiving++;
            $this->dice--;
        } 
        if($result == 6){
            $this->point++;
            $this->dice--;
        }
    }

    private function preparation(){
        $this->results        = [];
        $this->diceGiving     = 0;
        $this->additionalDice =  0;
    }
    
}