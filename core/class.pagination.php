<?php
/*

SAMPLE USAGE: 

	<?PHP
	    // First off, how many items per page?
	    $per_page = 4;

	    // Next, get the total number of items in the database
	    $num_videos = $db->getValue("SELECT COUNT(*) FROM videos where status = 'approved' ORDER BY dt");

	    // Initialize the Pager object
	    $pager = new Pager($_GET['p'], $per_page, $num_videos);

	    // Using the data that $pager calculated for us, select the appropriate records from the database
	    $videos = DBObject::glob('video', "SELECT * FROM videos WHERE status = 'approved' ORDER BY dt DESC LIMIT {$pager->first_record}, {$pager->per_page}");
	?>
	<html>
	<body>

	    <p>You are viewing videos <?PHP echo $pager->first_record; ?> through <?PHP echo $pager->last_record; ?>
	    of <?PHP echo $pager->num_records; ?> total.</p>

	    <?PHP foreach($videos as $v) : ?>
	    <!-- Do something with the video - display it perhaps -->
	    <?PHP endforeach; ?>

	    <!-- Simple Previous/Next links -->
	    <a href="some-page.php?p=<?PHP echo $p->prev_page(); ?>">Previous Page</a>
	    <a href="some-page.php?p=<?PHP echo $p->next_page(); ?>">Next Page</a>

	</body>
	</html>
	
*/

class Pagination implements Iterator
{
	// Stuff you set...
	public $page;		// Current page (will be recalculated if outside valid range)
	public $per_page;	 // Number of records per page
	public $num_records;  // Total number of records

	// Stuff we calculate...
	public $num_pages;	// Number of pages required to display $num_records records
	public $first_record; // Index of first record on current page
	public $last_record;  // Index of last record on current page

	private $records;	// Used when iterating over object

	// Initialize the pager object with your settings and calculate the resultant values
	public function __construct($page, $per_page, $num_records)
	{
		$this->page = $page;
		$this->per_page = $per_page;
		$this->num_records = $num_records;
		$this->calculate();
	}

	// Do the math.
	// Note: Pager always calculates there to be *at least* 1 page. Even if there are 0 records, we still,
	// by convention, assume it takes 1 page to display those 0 records. While mathematically stupid, it
	// makes sense from a UI perspective.
	public function calculate()
	{
		$this->num_pages = ceil($this->num_records / $this->per_page);
		
		if ($this->num_pages == 0) 
			$this->num_pages = 1;

		$this->page = intval($this->page);
		
		if ($this->page < 1) 
			$this->page = 1;
		
		if ($this->page > $this->num_pages) 
			$this->page = $this->num_pages;

		$this->first_record = (int) ($this->page - 1) * $this->per_page;
		$this->last_record  = (int) $this->first_record + $this->per_page - 1;
		
		if ($this->last_record >= $this->num_records) 
			$this->last_record = $this->num_records - 1;

		$this->records = range($this->first_record, $this->last_record, 1);
	}

	// Will return current page if no previous page exists
	public function prev_page()
	{
		return max(1, $this->page - 1);
	}

	// Will return current page if no next page exists
	public function next_page()
	{
		return min($this->num_pages, $this->page + 1);
	}

	// Is there a valid previous page?
	public function has_prev_page()
	{
		return $this->page > 1;
	}

	// Is there a valid next page?
	public function has_next_page()
	{
		return $this->page < $this->num_pages;
	}

	public function rewind()
	{
		reset($this->records);
	}

	public function current()
	{
		return current($this->records);
	}

	public function key()
	{
		return key($this->records);
	}

	public function next()
	{
		return next($this->records);
	}

	public function valid()
	{
		return $this->current() !== false;
	}
}
