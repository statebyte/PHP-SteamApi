<?php
/**
 * Discription:
 * PHP класс для работы со SteamApi а также SteamID
 * 
 * Version: v.1.0.0 
 * 
 * Где брать ключ:
 * {@link https://steamcommunity.com/dev/apikey}
 *
 * Официальная докуменация о SteamApi:
 * {@link https://developer.valvesoftware.com/wiki/Steam_Web_API}
 * 
 * Инструкция тут:
 * GitHub: {@link https://github.com/theelsaud/PHP-SteamApi}
 * 
 * @author FIVE
 * Website: {@link https://5ivestargaming.ru}
 */
class SteamApi
{ 
	// Входные данные
	public $api_key; 
	public $account_key;
	
	public $type_key;
	public $communityid; 

	const message_api = "<a href='https://steamcommunity.com/dev/apikey'>Steam Web API Key</a> не был задан...";
	const message_game = "ID игры не был задан...";

	public function __construct($account_key, $api_key = NULL)
	{
		if(empty($account_key))
		{
			throw new Exception("Не задан ключ...");
		}
		if(!empty($api_key))
			$this->api_key = $api_key;

		$this->account_key = $account_key;
		$this->check();
	}

	public function check()
	{
		$issid = substr($this->account_key, 0, 5);
		$isurl = substr($this->account_key, 0, 4);

		$pattern = "/^(7656119)([0-9]{10})$/";
		$iscid = preg_match($pattern, $this->account_key, $match);

		if($issid == 'STEAM'){

			$this->steamid = $this->account_key;
			$cid = $this->steamidto64($cid);
			$this->communityid = (int) $cid;

			$this->type_key = "steamid";
			return $this->data_return();

		}elseif($isurl == 'http'){

			$ifcid = strpos($this->account_key, '/profiles/');
			$ifurl = strpos($this->account_key, '/id/');


			if($ifcid){
				$isurl = stristr($this->account_key, '/profiles/');
				$isurl = substr($isurl, 10);
				$isur =  substr($isurl, -1); // Проверка на последний знак "/"
				if($isur == '/')
				{
					$isurl = substr($isurl, 0, -1);
				}
				$iscid = preg_match($pattern, $isurl, $match);

				if($iscid)
				{
					$this->communityid = (int) $isurl;

					$this->type_key = "url";
					return $this->data_return();
				}

				throw new Exception("Не удалось определить url");
			}

			if($ifurl){

				$cid = $this->account_key.'/?xml=1';
				$xml = simplexml_load_file($cid);
				$xml = (array) $xml->steamID64;
				$this->communityid = (int) $xml[0];

				$this->type_key = "url_xml";
				return $this->data_return();
			}

			throw new Exception("Не удалось определить url");

		}elseif($iscid){

			$this->communityid = (int) $this->account_key;

			$this->type_key = "communityid";
			return $this->data_return();
		}

		throw new Exception("Ключ является неверным");
	}
	
	public function data_return()
	{
		return array("communityid" => $this->communityid, "type" => $this->type_key );
	} 

	// STEAM ID

	public function render()
	{
		$data = array(
			"communityid" => $this->communityid,
			"steamid" => $this->steamid64to(),
			"steamid3" => $this->steamid64to32(),
			"url" => "https://steamcommunity.com/profiles/".$this->communityid,
		);

		return $data;
	}

	public function steamidto64()
	{
		$steamid = $this->steamid;
		// STEAM_X:Y:Z
		$X = substr($steamid, 6, 1);
		$Y = substr($steamid, 8, 1);
		$Z = substr($steamid, 10);
		$V = hexdec('0x0110000100000000');
		$steam64 = $Z*2+$V+$Y;

		return $steam64;
	}

	public function steamid64to()
	{
		$steamid64 = $this->communityid;
		$pattern = "/^(7656119)([0-9]{10})$/";
		preg_match($pattern, $steamid64, $match);
		$const1 = 7960265728;
		$const2 = "STEAM_1:";
		if ($const1 <= $match[2]) 
		{
		    $a = ($match[2] - $const1)%2;
		    $b = ($match[2] - $const1 - $a)/2;
		    $steamid = $const2.$a.':'.$b;
		}

		return $steamid;
	}

	public function steamid64to32()
	{
		$steamid = $this->steamid64to();

		$Y = substr($steamid, 8, 1);
		$Z = substr($steamid, 10);
		$account_id = $Y+($Z*2);

		return $account_id;
	}

	// STEAM API

	public function GetPlayerSummaries()
	{
		if(empty($this->api_key))
		throw new Exception($this->message_api);

		$url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$this->api_key."&steamids=".$this->communityid;
		$urljson = file_get_contents($url);
		$data = (array) json_decode($urljson)->response->players[0];

		return $data;
	}

	public function GetPlayerBans()
	{
		if(empty($this->api_key))
		throw new Exception(self::message_api);

		$url = "https://api.steampowered.com/ISteamUser/GetPlayerBans/v1/?key=".$this->api_key."&steamids=".$this->communityid;
		$urljson = file_get_contents($url);
		$data = (array) json_decode($urljson)->players[0];

		return $data;
	}

	public function GetFriendList()
	{
		if(empty($this->api_key))
		throw new Exception(self::message_api);

		$url = "https://api.steampowered.com/ISteamUser/GetFriendList/v0001/?key=".$this->api_key."&steamid=".$this->communityid."&relationship=friend";
		$urljson = file_get_contents($url);
		$data = (array) json_decode($urljson)->friendslist->friends;

		return $data;
	}

	public function GetUserStatsForGame($gameid = null)
	{
		if(empty($this->api_key))
		throw new Exception(self::message_api);

		if(empty($gameid))
		throw new Exception(self::message_game);

		$url = "https://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v0002/?appid=".$gameid."&key=".$this->api_key."&steamid=".$this->communityid;
		$urljson = file_get_contents($url);
		$data = (array) json_decode($urljson)->playerstats;

		return $data;
	}

	public function GetPlayerAchievements($gameid = null)
	{
		if(empty($this->api_key))
		throw new Exception(self::message_api);

		if(empty($gameid))
		throw new Exception(self::message_game);

		$url = "https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v0001/?appid=".$gameid."&key=".$this->api_key."&steamid=".$this->communityid;
		$urljson = file_get_contents($url);
		$data = (array) json_decode($urljson)->playerstats;

		return $data;
	}

	public function GetOwnedGames()
	{
		if(empty($this->api_key))
		throw new Exception(self::message_api);

		$url = "https://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=".$this->api_key."&steamid=".$this->communityid;
		$urljson = file_get_contents($url);
		$data = (array) json_decode($urljson)->response;

		return $data;
	}

	public function GetRecentlyPlayedGames()
	{
		if(empty($this->api_key))
		throw new Exception(self::message_api);

		$url = "https://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/?key=".$this->api_key."&steamid=".$this->communityid;
		$urljson = file_get_contents($url);
		$data = (array) json_decode($urljson)->response;

		return $data;
	}

	public function IsPlayingSharedGame($gameid = null)
	{
		if(empty($this->api_key))
		throw new Exception(self::message_api);

		if(empty($gameid))
		throw new Exception(self::message_game);

		$url = "https://api.steampowered.com/IPlayerService/IsPlayingSharedGame/v0001/?key=".$this->api_key."&steamid=".$this->communityid."&appid_playing=".$gameid;
		$urljson = file_get_contents($url);
		$data = (array) json_decode($urljson)->response;

		return $data;
	} 

	public function GetSchemaForGame($gameid = null)
	{
		if(empty($this->api_key))
		throw new Exception(self::message_api);

		if(empty($gameid))
		throw new Exception(self::message_game);

		$url = "http://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/?key=".$this->api_key."&appid=".$gameid;
		$urljson = file_get_contents($url);
		$data = (array) json_decode($urljson);

		return $data;
	}
}