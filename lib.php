<?php

$GLOBALS['exclude_servers'] = ['.', '..', 'example.server.json'];

class Answers
{

    public $A;
    private $config_file;
    public $next_answer;

    function __construct()
    {
        $this->config_file = __DIR__ . "/answers.json";
        $this->A = [];
        if (is_file($this->config_file))
            $this->A = json_decode(file_get_contents($this->config_file), true);
        else
            $this->save();
    }

    function save()
    {
        file_put_contents($this->config_file, json_encode($this->A));
    }

    function do_we_know($key)
    {
        $resp = isset($this->A[$key]);
        if ($resp)
            $this->next_answer = $this->A[$key];
        return $resp;
    }

    function then_tell_me()
    {
        return $this->next_answer;
    }

    function remember($key, $val)
    {
        $this->A[$key] = $val;
    }
}

function prompt($msg)
{
    echo $msg;
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));

    fclose($handle);
    return $line;
}

class FtpServer
{
    private $connectionID;
    private $ftpSession = false;
    private $blackList = array('.', '..', 'Thumbs.db', '.git');
    public function __construct($ftpHost = "")
    {
        if ($ftpHost != "") $this->connectionID = ftp_connect($ftpHost);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function connect($ftpHost)
    {
        $this->disconnect();
        $this->connectionID = ftp_connect($ftpHost);
        return $this->connectionID;
    }

    public function login($ftpUser, $ftpPass)
    {
        if (!$this->connectionID) throw new Exception("Connection not established.", -1);
        $this->ftpSession = ftp_login($this->connectionID, $ftpUser, $ftpPass);
        ftp_pasv($this->connectionID, true);
        return $this->ftpSession;
    }

    public function disconnect()
    {
        if (isset($this->connectionID)) {
            ftp_close($this->connectionID);
            unset($this->connectionID);
        }
    }

    public function send_recursive_directory($localPath, $remotePath)
    {
        return $this->recurse_directory($localPath, $localPath, $remotePath);
    }

    private function recurse_directory($rootPath, $localPath, $remotePath)
    {
        $errorList = array();
        if (!is_dir($localPath)) throw new Exception("Invalid directory: $localPath");
        chdir($localPath);
        $directory = opendir(".");
        while ($file = readdir($directory)) {
            if (in_array($file, $this->blackList)) continue;
            if (is_dir($file)) {
                $errorList["$remotePath/$file"] = $this->make_directory("$remotePath/$file");
                $errorList[] = $this->recurse_directory($rootPath, "$localPath/$file", "$remotePath/$file");
                chdir($localPath);
            } else {
                echo "Uploading: $localPath/$file --> $remotePath/$file\n";
                $errorList["$remotePath/$file"] = $this->put_file("$localPath/$file", "$remotePath/$file");
            }
        }
        return $errorList;
    }

    public function make_directory($remotePath)
    {
        $error = "";
        try {
            @ftp_mkdir($this->connectionID, $remotePath);
        } catch (Exception $e) {
            if ($e->getCode() == 2) $error = $e->getMessage();
        }
        return $error;
    }

    public function put_file($localPath, $remotePath)
    {
        $error = "";
        try {
            ftp_put($this->connectionID, $remotePath, $localPath, FTP_BINARY);
        } catch (Exception $e) {
            if ($e->getCode() == 2) $error = $e->getMessage();
        }
        return $error;
    }
}

function bigpatch_ftp_upload($if, $server)
{ //server-* search all matching servers
    $dir = __DIR__ . "\\servers";
    $valid_servers = [];
    if(strpos($server, '*')===false)
        $valid_servers[] = "$dir\\$server.server.json";
    else{
        $scan = array_diff(scandir($dir), $GLOBALS['exclude_servers']);
        $search = str_replace('*', '', $server);
        foreach($scan as $server_file){
            if(strpos($server_file, $search)!==false)
                $valid_servers[] = "$dir\\$server_file";
        }
    }

    print_r($valid_servers);
    if(prompt("Continue with these? [y,n]")!='y'){
        echo "Aborting multi server upload\n";
        return false;
    }

    foreach($valid_servers as $server_file){
        echo "Using $server_file\n";
        if (!file_exists($server_file)) {
            echo "Fatal error: $server not found\n";
            return false;
        }
        $server_ = json_decode(file_get_contents($server_file), true);
    
        $ftp = new FtpServer($server_['hostname']);
        $ftpSession = $ftp->login($server_['username'], $server_['password']);
        if (!$ftpSession) {
            echo "Failed to connect.";
            return false;
        }
    
        $errorList = $ftp->send_recursive_directory($if, $server_['remote_folder']);
        print_r($errorList);
    
        $ftp->disconnect();
    }
    

    return true;
}

function bigpatch_ask_server()
{
    $dir = __DIR__ . '\\servers';
    echo " Listing your servers:\n[$dir]\n\n";
    $scan = array_diff(scandir($dir), $GLOBALS['exclude_servers']);
    $answers = [];
    if (count($scan) == 0)
        die("ERROR: You have not configured any server\n");
    $i = 0;
    foreach ($scan as $filename) {
        $i++;
        $server = explode('.', $filename)[0]; // '.' IS NOT ALLOWED AS SERVER NAME
        echo "  [" . ($i) . "] --> $server\n";
        $answers[$i] = $server;
    }
    echo "\n";
    $first = true;
    do {
        if($first)
            echo "> Select upload server\n";
        else
            echo "> Wrong answer: $answer\n";
        $first = false;
        $answer = prompt('Number of the server? [' . implode(', ', array_keys($answers)) . '] ');
        if(is_numeric($answer) && intval($answer) === 0)
            die("Bye");
        $is_search = strlen($answer) > 1 && strpos($answer, '*')!==false;
    } while (!$is_search && !isset($answers[$answer]));
    return $is_search ? $answer : $answers[$answer];
}
