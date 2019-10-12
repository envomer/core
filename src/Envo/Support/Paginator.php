<?php

namespace Envo\Support;

class Paginator
{
	public $first;
	public $before;
	public $last;
	public $next;
	public $total_items;
	public $total_pages;
	public $limit;
	public $data;
	public $current;
	
	/**
	 * @param     $data
	 * @param     $total
	 * @param int $page
	 * @param int $per_page
	 */
	public function __construct($data, $total = null, $page = 1, $per_page = 50)
	{
    	$this->first = 1;

		if ( $total === null ) {
			$total = count($data);
		}
    	
    	if ( $page > 1 ) {
    		$this->before = $page - 1;
		} else {
    		$this->before = 1;
		}

    	$this->current = $page;

    	// $this->current = $page;
    	$this->last = ceil(($total ?:1) / ($per_page ?: 1)) ?: 1;
    	if ( $this->current < $this->last ) {
    		$this->next = $this->current + 1;
		} else {
    		$this->next = $this->last;
		}
    	
    	$this->total_pages = $this->last;
    	$this->total_items = $total;
    	$this->limit = $per_page;

	    $this->data = $data;

	    return $this;
	}

}