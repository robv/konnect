<?php
	// The Pager class handles the (basic) display and logic for creating the paging HTML often found
	// on a search results page or any record set that is broken up across multiple pages.
	// Can output either a simple "Page 2 of 5" style message or a more complex " << 1 2 3 [4] 5 6 7 >>" one.
	
	class Pager
	{
		// Insert [#] for page number placeholder
		public $link = "?page=[#]";

		public $perPage = 10; // Number of items per page
		public $numPages; // ceil($count / $perPage)

		// Prev/Next HTML when *not* linked
		public $prevMark = '';
		public $nextMark = '';

		// Prev/Next HTML when linked
		public $prevMarkLinked = '&#171; Previous';
		public $nextMarkLinked = 'Next &#187;';

		// Number of pages to show to left and right of current
		public $radius    = 5; 

		// Tag to wrap page numbers with (advanced mode only)
		// Use a complete tag such as "<span>" not just "span".
		// This lets you do stuff like "<span class='foo'>"
		public $wrapTag = "<span>";

		// HTML between page numbers (advanced mode only)
		public $seperator = " | ";

		// You typically don't need to modify these
		public $prev;
		public $cur;
		public $next;
		public $count; // Total number of items (not pages!)
		public $limit; // Limit value you should pass to your SELECT query

		function __construct($cur, $count, $link = null, $per_page = null)
		{
			$this->cur = $cur;

			if(is_numeric($count)) $this->count  = $count;
			if(is_array($count)) $this->count    = count($count);
			if(is_resource($count)) $this->count = mysql_num_rows($count);

			if(!is_null($link)) $this->link        = $link;
			if(!is_null($per_page)) $this->perPage = $per_page;
		}

		// If you set any variables after creating the Pager object,
		// you can call this function to redo the math.
		function calculate()
		{
			$this->numPages = ceil($this->count / $this->perPage);
			if($this->numPages < 1) $this->numPages = 1;

			if($this->cur < 1) $this->cur = 1;
			if($this->cur > $this->numPages) $this->cur = $this->numPages;

			$this->limit = ($this->cur - 1) * $this->perPage;

			if($this->cur == 1)
			{
				$this->prev = 1;
				$this->next = ($this->numPages > 1) ? 2 : 1;
			}
			elseif($this->cur == $this->numPages)
			{
				$this->prev = $this->cur - 1;
				$this->next = $this->numPages;
			}
			else
			{
				$this->prev = $this->cur - 1;
				$this->next = $this->cur + 1;
			}
		}

		function simple()
		{
			$this->calculate();

			if($this->cur == 1 && $this->numPages == 1)
				return "{$this->prevMark} Page 1 of 1 {$this->nextMark}";
			elseif($this->cur == 1 && $this->numPages > 1)
			{
				$href = $this->makeLink($this->next);
				return "<span id='pager_prev'>{$this->prevMark}</span> Page 1 of {$this->numPages} <a href='$href' id='pager_next'>{$this->nextMarkLinked}</a>";
			}
			elseif($this->cur == $this->numPages)
			{
				$href = $this->makeLink($this->prev);
				return "<a href='$href' id='pager_prev'>{$this->prevMarkLinked}</a> Page {$this->cur} of {$this->numPages} <span id='pager_next'>{$this->nextMark}</span>";
			}
			else
			{
				$href_prev = $this->makeLink($this->prev);
				$href_next = $this->makeLink($this->next);
				return "<a href='$href_prev' id='pager_prev'>{$this->prevMarkLinked}</a> Page {$this->cur} of {$this->numPages} <a href='$href_next' id='pager_next'>{$this->nextMarkLinked}</a>";
			}
		}
	
		function advanced()
		{
			$this->calculate();

			$start = $this->cur - $this->radius;
			if($start < 1) $start = 1;

			$stop = $this->cur + $this->radius;
			if($stop > $this->numPages) $stop = $this->numPages;

			if(preg_match('@<([a-zA-Z]+)@', $this->wrapTag, $matches) == 1)
			{
				$opening_tag = $this->wrapTag;
				$closing_tag = "</{$matches[1]}>";
			}
			else
			{
				$opening_tag = '';
				$closing_tag = '';
			}

			$numbers = array();
			for($i = $start; $i <= $stop; $i++)
			{
				$href = $this->makeLink($i);
			
				if($i == $this->cur)
					$numbers[] = "$opening_tag<a href='$href' class='pager_number pager_current'>$i</a>$closing_tag";
				else
					$numbers[] = "$opening_tag<a href='$href' class='pager_number'>$i</a>$closing_tag";
			}
		
			$numbers = implode($this->seperator, $numbers);

			if($this->cur == 1 && $this->numPages == 1)
				return "{$this->prevMark} $numbers {$this->nextMark}";
			elseif($this->cur == 1 && $this->numPages > 1)
			{
				$href = $this->makeLink($this->next);
				return "<span class='pager_prev'>{$this->prevMark}</span> $numbers <a href='$href' class='pager_next'>{$this->nextMarkLinked}</a>";
			}
			elseif($this->cur == $this->numPages)
			{
				$href = $this->makeLink($this->prev);
				return "<a href='$href' class='pager_prev'>{$this->prevMarkLinked}</a> $numbers <span class='pager_next'>{$this->nextMark}</span>";
			}
			else
			{
				$href_prev = $this->makeLink($this->prev);
				$href_next = $this->makeLink($this->next);
				return "<a href='$href_prev' class='pager_prev'>{$this->prevMarkLinked}</a> &#8212; $numbers &#8212; <a href='$href_next' class='pager_next'>{$this->nextMarkLinked}</a>";
			}
		}

		function makeLink($page_num)
		{
			return str_replace("[#]", $page_num, $this->link);
		}
	}