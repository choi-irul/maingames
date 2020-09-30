<?php

namespace App\Console\Commands;

use App\Library\Player;
use Illuminate\Console\Command;

class MainGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'main:games';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $players = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $player_count = $this->ask('Masukkan Jumlah Pemain?');
        $dices        = $this->ask('Masukkan Jumlah Dadu?');

        $this->init($player_count, $dices);

        $iteration = 0;
        $activePlayer = $this->activePlayer();
        while($activePlayer->count() > 1){
            $this->line('');
            $this->info("Giliran ke-" . ($iteration+1) . ' => Pemain Aktif: ' . $activePlayer->count());

            $this->playNow($activePlayer);

            $activePlayer = $this->activePlayer();
            $iteration++;
        }
        $final = $this->finalResult();
        $this->line("");
        $this->info("HASIL AKHIR");
        $this->line("Pemenang adalah Pemain ke-" . ($final[0]+1) . " dengan Point: " . $final[1]);
    }

    private function init($player_count, $dices){
        $players = [];
        for ($i=0; $i < $player_count; $i++) { 
            $player    = new Player($dices);
            $players[] = $player;
        }
        $this->players = collect($players);
    }

    private function playNow($players){
        foreach ($players as $key => $player) {
            $player->rollAllTheDice();
        }
        $this->evaluation($players);
        $this->writeOutput();
        $this->removePlayer($players);
    }
    
    private function evaluation($players){
        foreach ($players as $key => $player) {
            if($player->diceGiving > 0){
                $nextPlayer = $this->nextPlayer($players ,$key);
                if(!$nextPlayer){
                    $nextPlayer = $players->get(0);
                }

                $nextPlayer->addDice($player->diceGiving);
            }
        }
    }

    private function removePlayer($players){
        foreach ($players as $key => $player) {
            if($player->dice == 0){
                $player->done();
            }
        }
    }

    private function activePlayer()
    {
        return $this->players->filter(function ($player) {
            if($player->dice > 0) return $player;
        });
    }

    private function nextPlayer($players, $key){
        $keys  = $players->keys();
        $index = array_search($key, $keys->all()) + 1;
        if($index < $keys->count()){
            $nextKey = $keys[array_search($key, $keys->all()) + 1];
        }else{
            $nextKey = $keys[0];
        }
        
        return $players->get($nextKey);
    }

    private function finalResult(){
        $points = $this->players->map(function ($player) {
            return $player->point + $player->dice;
        });

        return [array_search($points->max(), $points->all()) ,$points->max()];
    }
    
    private function writeOutput(){
        $headers = ['Pemain', 'Dadu', 'Hasil', 'Dadu yang diberikan', 'Dadu yang didapat' , 'Sisa Dadu', 'Poin'];
        $rows = $this->players->map(function ($player, $index) {
            return ["Pemain " . ($index+1), count($player->results), implode(',', $player->results), $player->diceGiving, $player->additionalDice, $player->dice, $player->point];
        });
        $this->table($headers, $rows);
    }
}
