<?php
/**
 *
 * @author Rouven Wachhaus <rouven@wachhaus.xyz>
 * @todo original author?
 * @todo doc
 * @todo cc wrt variable/method names
 */
/**
 * Class Tpl
 */
class Tpl
{
    // The template variables, used to store all of the data going into template
    private $tplNames = array();
    private $tplValues = array();

    // Variables for blocks in the templates
    private $rawBlocks = array();
    private $tplBlocks = array(); // public to test whether a block has been created
    private $instance;
    private $inline; // integer for making in-line blocks

    // The current index of the tpl vars
    private $index;

    // The actual template itself
    private $template;

    // Base directory, where all the template files are stored
    private $baseDir;

    /**
     * Initialization function
     *
     * @param String $baseDir
     * @return tpl
     */
    function __construct($baseDir)
    {
        $this->index = 0;
        $this->instance = 0;
        $this->inline = 0;
        $this->template = '';
        $this->baseDir = $baseDir;
    }

    /**
     * Attempts to set an already defined block variable
     *
     * @param String $sVar          The name of the variable we want to replace
     * @param String $sVal          The current value of the variable
     * @param String $nVal          The new value you want
     * @return bool true on success
     */
    public function setExisting($sVar, $sVal, $nVal)
    {
        // Search for this variable name
        foreach ($this->tplNames as $key => $varName) {

            // Check that the variable name and values match
            if (strpos($varName, '{' . $sVar . ':') !== false && $this->tplValues[$key] == $sVal) {
                // We have a match, change this value
                $this->tplValues[$key] = $nVal;

                return true;
            }
        }

        return false;
    }

    /**
     * Sets values to template variables
     *
     * @param String[] $variables
     * @return void
     */
    public function set($variables = array())
    {
        // Exit if no values were passed
        //if(empty($variables)) return;

        // Check if this is using the syntax set($name, $value)
        if (!is_array($variables)) {
            $args = func_get_args();
            $variables = array($args[0] => $args[1]);
        }

        // store the variables
        foreach ($variables as $name => $value) {
            $this->tplNames[$this->index] = '{' . $name . '}';
            $this->tplValues[$this->index] = $value;
            $this->index++;
        }

        return;
    }

    /**
     * Defines an instance of a block
     *
     * @param String $blockName
     * @param String[] $variables
     * @return void
     */
    public function newBlock($blockName, $variables = array())
    {
        // Make sure this block has been defined
        if (!isset($this->rawBlocks[$blockName]))
            die('Block ' . $blockName . ' has not been defined.');

        // Add this block to our template blocks
        $this->tplBlocks[$blockName][$this->instance] = '';

        // Get the raw block
        $rawBlock = $this->rawBlocks[$blockName];

        // Add the variables to this block
        if (!empty($variables)) {
            foreach ($variables as $name => $value) {
                $this->set(array('{$name}:' . $this->instance => $value));

                // Replace the reference to this variable to the instanced variable
                $rawBlock = str_replace('{' . $name . '}', '{' . $name . ':' . $this->instance . '}', $rawBlock);
            }
        }

        // Store the block
        $this->tplBlocks[$blockName][$this->instance] = $rawBlock;
        $this->instance++;

        return;
    }

    /**
     * Loads a template
     *
     * @param String $template
     */
    public function loadTemplate($template)
    {
        // Make sure the requested template exists
        if (!file_exists($this->baseDir . '/{' . $template. '}'))
            die('Cannot find {' . $template . '}');

        // Make sure the template is not zero length
        if (filesize($this->baseDir . "/{$template}") == 0)
            die("Template {$template} cannot be zero length");

        $path = $this->baseDir."/{$template}";
        // Read the entire file
        $f = fopen($path, "r");
        $data = fread($f, filesize($path));
        fclose($f);

        // Get any included files
        $files = array();
        preg_match_all("/{INCLUDE ([a-zA-Z0-9_-]*.tpl)}/", $data, $files);

        // Replace any includes with the actual file
        if (!empty($files)) {
            foreach ($files[1] as $file) {
                // Make sure the file exists before reading it
                if (!file_exists($this->baseDir."/{$file}"))
                    die("Cannot find included template {$file}");

                // Read the included file
                $f = fopen($this->baseDir."/{$file}", "r");
                $include_data = fread($f, filesize($this->baseDir."/{$file}"));
                fclose($f);

                // Store the included file in our data variable
                $data = str_replace("{INCLUDE ".$file."}", $include_data, $data);

                // Clear the memory for the included file
                unset($include_data);
            }
        }

        // Look for any blocks
        $blocks = array();
        preg_match_all("/{BLOCK ([a-zA-Z0-9_]*)}/", $data, $blocks);

        // Load any blocks that exist
        if (!empty($blocks)) {
            foreach ($blocks[1] as $block) {
                // Need to find where this block ends
                $start = strpos($data, "{BLOCK {$block}}") + (8 + strlen($block));
                $end = strpos($data, "{END BLOCK}", $start);

                // Load the raw block
                $this->rawBlocks[$block] = substr($data, $start, $end-$start);

                // Replace the block in the template with just a reference
                $data = substr($data, 0, ($start - (8 + strlen($block))))."{BLOCK {$block}}".substr($data, ($end + 11));
            }
        }

        /********/

        // Look for any in-line within the existing blocks
        foreach ($this->rawBlocks as $blockName => $blockData) {
            $sections = array();
            preg_match_all("/{NEWBLOCK ([a-zA-Z0-9_ ,='?]*)}/", $blockData, $sections);

            //echo "<pre>$data</pre>";

            // Create any in-line blocks that are called for
            if (!empty($sections)) {
                foreach ($sections[1] as $sec) {
                    // Extract the command line
                    $secArr = explode(" ", $sec);
                    $secName = $secArr[0];

                    // Make sure this block exists
                    if (!isset($this->rawBlocks[$secName]))
                        die("Block {$secName} has not been defined.");

                    // Make sure this block is not the same name as the inline block
                    // Otherwise we could have some problems
                    if ($secName == $blockName)
                        die("Cannot create an inline block inside a block with the same name ({$blockName})");

                    $inlineData = $this->rawBlocks[$secName];
                    unset($secArr[0]);
                    $vars = implode(" ", $secArr);

                    // Extract the variables
                    $variables = array();        // list of found variables
                    $offset = 0;                 // string offset
                    $pname = strpos($vars, "="); // position of equals
                    $inside = false;             // Inside the quotes?
                    $this->inline++;

                    while ($pname !== false) {
                        $varName = substr($vars, $offset, $pname - $offset);
                        $next = strpos($vars, "'", $offset);  // next position of '
                        $s = 0;                               // Initialize start and end vars
                        $e = 0;

                        while ($next !== false) {
                            if (!$inside) {
                                $s = $next + 1;
                                $inside = true;
                                $offset = $next + 1;
                            } else {
                                $e = $next;
                                $inside = false;
                                $offset = $next + 1;
                                break;
                            }

                            $next = strpos($vars, "'", $offset);
                        }

                        if ($s != 0 && $e != 0 && !$inside) {
                            $varValue = substr($vars, $s, $e - $s);
                            //$variables[$varName] = $varValue;
                            $this->set(array("{$varName}:inline".$this->inline => $varValue));
                            $inlineData = str_replace("{{$varName}}", "{{$varName}:inline".$this->inline."}", $inlineData);
                        } else {
                            if ($s == 0)
                                die("Starting quote (') missing for variable in NEWBLOCK $secName");

                            if ($e == 0)
                                die("Ending quote (') missing for variable in NEWBLOCK $secName");

                            if ($inside)
                                die("Odd number of quotes (') for variables in NEWBLOCK $secName");
                        }

                        // Check for a space after variable
                        while (substr($vars, $offset, 1) == " ") {
                            $offset += 1;
                        }

                        $pname = strpos($vars, "=", $offset);
                    }

                    // Find where this in-line block is
                    $start = strpos($blockData, "{NEWBLOCK $sec}");
                    $end = $start + strlen("{NEWBLOCK $sec}");

                    // Replace in-line newblock with the actual block
                    $blockData = substr($blockData, 0, $start).$inlineData.substr($blockData, $end);

                    // Redefine this block with the in-line reference
                    //$this->rawBlocks["inline".$this->inline] = $this->rawBlocks[$secName];

                    // Create the block
                    //$this->newBlock("inline".$this->inline, $variables);
                }
            }

            // Update this rawblock
            $this->rawBlocks[$blockName] = $blockData;
        }

        /********/

        // Look for any in-line newBlocks
        $sections = array();
        preg_match_all("/{NEWBLOCK ([a-zA-Z0-9_ ,='?]*)}/", $data, $sections);

        //echo "<pre>$data</pre>";

        // Create any in-line blocks that are called for
        if (!empty($sections)) {
            foreach ($sections[1] as $sec) {
                // Extract the command line
                $secArr = explode(" ", $sec);
                $secName = $secArr[0];

                // Make sure this block exists
                if (!isset($this->rawBlocks[$secName]))
                    die("Block {$secName} has not been defined.");

                unset($secArr[0]);
                $vars = implode(" ", $secArr);

                // Extract the variables
                $variables = array();        // list of found variables
                $offset = 0;                 // string offset
                $pname = strpos($vars, "="); // position of equals
                $inside = false;             // Inside the quotes?

                while ($pname !== false) {
                    $varName = substr($vars, $offset, $pname - $offset);
                    $next = strpos($vars, "'", $offset);  // next position of '
                    $s = 0;                               // Initialize start and end vars
                    $e = 0;

                    while ($next !== false) {
                        if (!$inside) {
                            $s = $next + 1;
                            $inside = true;
                            $offset = $next + 1;
                        } else {
                            $e = $next;
                            $inside = false;
                            $offset = $next + 1;
                            break;
                        }

                        $next = strpos($vars, "'", $offset);
                    }

                    if ($s != 0 && $e != 0 && !$inside) {
                        $varValue = substr($vars, $s, $e - $s);
                        $variables[$varName] = $varValue;
                    } else {
                        if ($s == 0)
                            die("Starting quote (') missing for variable in NEWBLOCK $secName");

                        if ($e == 0)
                            die("Ending quote (') missing for variable in NEWBLOCK $secName");

                        if ($inside)
                            die("Odd number of quotes (') for variables in NEWBLOCK $secName");
                    }

                    // Check for a space after variable
                    while (substr($vars, $offset, 1) == " ") {
                        $offset += 1;
                    }

                    $pname = strpos($vars, "=", $offset);
                }

                // Find where this in-line block is
                $start = strpos($data, "{NEWBLOCK $sec}");
                $end = $start + strlen("{NEWBLOCK $sec}");

                // Replace in-line newblock with block reference
                $this->inline++;
                $data = substr($data, 0, $start)."{BLOCK inline".$this->inline."}".substr($data, $end);

                // Redefine this block with the in-line reference
                $this->rawBlocks["inline".$this->inline] = $this->rawBlocks[$secName];

                // Create the block
                $this->newBlock("inline".$this->inline, $variables);
            }
        }

        // Store the template
        $this->template = $data;

        return;
    }

    /**
     * Parses a template and outputs it to the screen
     *
     * @param bool $return if set to true parse will return the data instead of printing it
     * @return void
     */
    public function parse($return = false)
    {
        // Make sure a template has been loaded
        if (strlen($this->template) == 0)
            die("No template has been loaded.");

        // Get the template
        $data = $this->template;

        // Look for any blocks
        $blocks = array();
        preg_match_all("/{BLOCK ([a-zA-Z0-9_]*)}/", $data, $blocks);

        // Replace any blocks with there assigned data
        if (!empty($blocks)) {
            foreach ($blocks[1] as $block) {
                // Need to find where this block ends
                $start = strpos($data, "{BLOCK {$block}}");
                $end = $start + 8 + strlen($block);

                // Check to see if this block was defined
                $theData = "";
                if (isset($this->tplBlocks[$block])) {
                    foreach ($this->tplBlocks[$block] as $block_data) {
                        $theData .= $block_data;
                    }
                }

                // Replace the block in the template with the data
                $data = substr($data, 0, $start).$theData.substr($data, $end);
            }
        }

        // Begin filling in the variables with there values
        if (!empty($this->tplNames)) {
            $data = str_replace($this->tplNames, $this->tplValues, $data);
        }

        // Erase any variables not used
        $data = preg_replace("/{([a-zA-Z0-9_ ]*)}/", "", $data);

        // Dump the template to screen
        if (!$return)
            echo $data;
        else
            return $data;
        //eval($data);

        unset($data);

        return;
    }
}