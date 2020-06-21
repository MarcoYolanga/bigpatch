<?php
/**
 * Bigpatch CORE
 * 
 * Nessun riferimento ai singoli progetti qui, applica un progetto nuovo su bigpatch.bat
 */

class Answers{
  
  public $A;
  private $config_file;
  public $next_answer;

  function __construct()
  {
    $this->config_file = __DIR__."/answers.json";
    $this->A = [];
    if(is_file($this->config_file))
      $this->A = json_decode(file_get_contents($this->config_file), true);
    else
      $this->save();
  }

  function save(){
    file_put_contents($this->config_file, json_encode($this->A));
  }

  function do_we_know($key){
    $resp = isset($this->A[$key]);
    if($resp)
      $this->next_answer = $this->A[$key];
    return $resp;
  }

  function then_tell_me(){
    return $this->next_answer;
  }

  function remember($key, $val){
    $this->A[$key] = $val;
  }

}

function prompt($msg){
  echo $msg;
  $handle = fopen ("php://stdin","r");
  $line = trim(fgets($handle));

  fclose($handle);
  return $line;
}

$if = $argv[1];
$of = $argv[2];


mkdir($of);

$idisk = substr($if, 0, 2);

$git = shell_exec("$idisk && cd \"$if\" && git status --porcelain=v2");

echo "\nBig Patch v1.0\n\nWith git status:\n".shell_exec("$idisk && cd \"$if\" && git status")."\n$git\n\n------------------------------------------------------------\n";

$git = explode("\n", $git);
$files = [];
foreach ($git as $entry) {
  $row = explode(' ', $entry);
  if(($size = count($row)) > 0){
    $file = $row[$size-1];
    if(strpos($file, '.')!==false){
      $files[] = str_replace('/', '\\', $file);
    }
  }
}

natsort($files);
print_r($files);

$accepted_files = [];
$answers = new Answers();
foreach ($files as $n => $file) {
  $resp = false;
  if($answers->do_we_know($file))
    $resp = $answers->then_tell_me();

  switch ($resp === false ? prompt("Include [$n] => $file ? [e = exclude]") : $resp) {
    case 'E':
    case 'e':
      //exclude
      if($resp === false)
        $answers->remember($file, 'e');
      else echo "Auto excluded: $file\n";
      break;

    default:
      //add
      $answers->remember($file, 'a');
      $accepted_files[] = $file;
      break;
  }
}
$answers->save();
echo "\n\n------------------------------------------------------------\n";
$copy_array = [];
foreach ($accepted_files as $file) {
  $file_to_copy = "$if\\$file";
  if(!file_exists($file_to_copy)){
    echo ("Bigpatch: $file_to_copy not found\n");
  }
  $fulldir = dirname($file_to_copy);
  $filename = str_replace($fulldir, '', $file_to_copy);
  $dir = str_replace($if, '', $fulldir);
  if(!is_dir($of.$dir) && !mkdir($of.$dir, 0777, true))
    echo "\nERROR: MAKE DIR $of$dir\n";
  $target = $of.$dir.$filename;
  $copy_array[$file_to_copy] = $target;
}

foreach ($copy_array as $file_to_copy => $target) {
  echo "Copying $file_to_copy -> $target\n";
  copy($file_to_copy, "$target");
}
echo "\nSuccess,\nCAREFULLY CHECK YOUR PATCH FILES BEFORE USE!\n";
