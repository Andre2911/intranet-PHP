<?php
namespace App\Helpers;

class PcCliente {
	public static function get_Cliente(){
		$cliente['ip'] = isset($_SERVER['HTTP_CLIENT_IP'])?$_SERVER['HTTP_CLIENT_IP']:isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];;

		//$cliente['host'] = @gethostbyaddr($cliente['ip']);
		//$cliente['host'] = shell_exec('host -W 1 '.$_SERVER['REMOTE_ADDR']);
		$cliente['host'] = '-';

		$macAddr=false;

		$arp = shell_exec('arp -a '.$cliente['ip']);
		$lines=explode("\n", $arp);

		#look for the output line describing our IP address
		//print_r($lines);
		foreach($lines as $line)
		{
		   $cols=preg_split('/\s+/', trim($line));

		   if ($cols[0]==$cliente['ip'])
		   {
		       $macAddr = $cols[1];
		   }
		}

		$cliente['mac'] = $macAddr;
		return $cliente;
	}
}