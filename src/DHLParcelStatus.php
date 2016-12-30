<?php
namespace MCS;

use DateTime;
use Exception;

use PHPHtmlParser\Dom;

class DHLParcelStatus{
    
    private $awb;

    private $delivered_states = [
        'delivered'    
    ];
    
    public $events = [];
    
    const URL = 'https://www.dhlparcel.nl/en/private/receiving/track-trace?tt=:awb';
    
    public function __construct($urlOrAwb)
    {
        if (filter_var($urlOrAwb, FILTER_VALIDATE_URL)) {
            $this->parse_query_string($urlOrAwb);
        } else {
            $this->awb = $urlOrAwb;    
        }
    }
    
    public function getStatus()
    {
        $dom = new Dom();
        $dom->load(
            str_replace(':awb', $this->awb, self::URL)
        );
        
        $states = $dom->find('td[class=definition]');
        $dates = $dom->find('td[class=date]');
        $times = $dom->find('td[class=time]');
        
        $counter = 0;
        
        foreach ($states as $state) {
            
            $this->events[] = [
                'state' => rtrim($state->text, ','),
                'delivered' => false,
                'timestamp' => DateTime::createFromFormat(
                    'j M Y H:i', $dates[$counter]->text . ' ' . $times[$counter]->text
                )
            ]; 
            
            foreach ($this->delivered_states as $delivered_state) {
                if (strpos(mb_strtolower($state->text), $delivered_state) !== false) {
                    $this->events[$counter]['delivered'] = true;   
                }
            }  
            
            ++$counter;
        }
        
        return $this->events;
    }
    
    private function parse_query_string($url)
    {
        $url = parse_url($url);

        if (!isset($url['query'])) {
            throw new Exception('No query string in url');
        } else {
            parse_str($url['query'], $query);
            if (isset($query['tt'])) {
                $this->awb = $query['tt'];    
            } else {
                throw new Exception('No awb found in query string');        
            }
        }
    }
    
}
