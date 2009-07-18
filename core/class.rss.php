<?PHP
class Rss
{
	public $title;
	public $link;
	public $description;
	public $language = 'en-US';
	public $pubDate;
	public $url;
	public $items;
	public $tags;
	public $useCDataTags;

	public function __construct()
	{
	    $this->items = array();
	    $this->tags  = array();
	    $this->useCDataTags = true;
	    $this->set_pub_date();
	    $this->url = $this->full_url();
	}

	public function add_item($item)
	{
	    $this->items[] = $item;
	}

	public function set_pub_date($date = null)
	{
	    if(is_null($date)) $date = time();
	    if(!ctype_digit($date)) $date = strtotime($date);
	    $this->pubDate = date('D, d M Y H:i:s O', $date);
	}

	public function add_tag($tag, $value)
	{
	    $this->tags[$tag] = $value;
	}

	public function load_recordset($result, $title, $link, $description, $pub_date)
	{
	    while($row = mysql_fetch_array($result, MYSQL_ASSOC))
	    {
	        $item = new Rss_Item();
	        $item->title       = $row[$title];
	        $item->link        = $row[$link];
	        $item->description = $row[$description];
	        $item->set_pub_date($row[$pub_date]);
	        $this->add_item($item);
	    }
	}

	public function out()
	{
	    $bad         = array('&', '<');
	    $good        = array('&#x26;', '&#x3c;');
	    $title       = str_replace($bad, $good, $this->title);
	    $description = str_replace($bad, $good, $this->description);

	    $out  = $this->header();
	    $out .= "<channel>\n";
	    $out .= "<title>" . $title . "</title>\n";
	    $out .= "<link>" . $this->link . "</link>\n";
	    $out .= "<description>" . $description . "</description>\n";
	    $out .= "<language>" . $this->language . "</language>\n";
	    $out .= "<pubDate>" . $this->pubDate . "</pubDate>\n";
	    $out .= '<atom:link href="' . $this->url . '" rel="self" type="application/rss+xml" />' . "\n";

	    foreach($this->tags as $k => $v)
	        $out .= "<$k>$v</$k>\n";

	    foreach($this->items as $item)
	        $out .= $item->out();

	    $out .= "</channel>\n";

	    $out .= $this->footer();

	    return $out;
	}

	public function serve($contentType = 'application/xml')
	{
	    $xml = $this->out();
	    header("Content-type: $contentType");
	    echo $xml;
	}

	private function header()
	{
	    $out  = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
	    $out .= '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
	    return $out;
	}

	private function footer()
	{
	    return '</rss>';
	}

	private function full_url()
	{
	    $s = empty($_SERVER['HTTPS']) ? '' : ($_SERVER['HTTPS'] == 'on') ? 's' : '';
	    $protocol = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos(strtolower($_SERVER['SERVER_PROTOCOL']), '/')) . $s;
	    $port = ($_SERVER['SERVER_PORT'] == '80') ? '' : (":".$_SERVER['SERVER_PORT']);
	    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
	}

	private function cdata($str)
	{
	    if($this->useCDataTags)
	    {
	        $str = '<![CDATA[' . $str . ']]>';
	    }
	    return $str;
	}
}

class Rss_Item
{
	public $title;
	public $link;
	public $description;
	public $pubDate;
	public $guid;
	public $tags;
	public $enclosureUrl;
	public $enclosureType;
	public $enclosureLength;
	public $useCDataTags;

	public function __construct()
	{
	    $this->useCDataTags = true;
	    $this->tags = array();
	    $this->set_pub_date();
	}

	public function set_pub_date($date = null)
	{
	    if(is_null($date)) $date = time();
	    if(!ctype_digit($date)) $date = strtotime($date);
	    $this->pubDate = date('D, d M Y H:i:s O', $date);
	}

	public function add_tag($tag, $value)
	{
	    $this->tags[$tag] = $value;
	}

	public function out()
	{
	    $bad         = array('&', '<');
	    $good        = array('&#x26;', '&#x3c;');
	    $title       = str_replace($bad, $good, $this->title);

	    $out  = "<item>\n";
	    $out .= "<title>" . $title . "</title>\n";
	    $out .= "<link>" . $this->link . "</link>\n";
	    $out .= "<description>" . $this->cdata($this->description) . "</description>\n";
	    $out .= "<pubDate>" . $this->pubDate . "</pubDate>\n";

	    if(is_null($this->guid))
	        $this->guid = $this->link;

	    $out .= "<guid>" . $this->guid . "</guid>\n";

	    if(!is_null($this->enclosureUrl))
	        $out .= "<enclosure url='{$this->enclosureUrl}' length='{$this->enclosureLength}' type='{$this->enclosureType}' />\n";

	    foreach($this->tags as $k => $v)
	        $out .= "<$k>$v</$k>\n";

	    $out .= "</item>\n";
	    return $out;
	}

	public function enclosure($url, $type, $length)
	{
	    $this->enclosureUrl    = $url;
	    $this->enclosureType   = $type;
	    $this->enclosureLength = $length;
	}

	private function cdata($str)
	{
	    if($this->useCDataTags)
	        $str = '<![CDATA[' . $str . ']]>';
	
	    return $str;
	}
}
