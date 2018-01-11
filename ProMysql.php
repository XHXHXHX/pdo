<?php

class ProMysql{

	private $db_host = '';
	private $db_username = '';
	private $db_password = '';
	private $db_databases = '';

	private $_opts_values=array(PDO::ATTR_PERSISTENT=>true,PDO::ATTR_ERRMODE=>2,PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES utf8');

	public $prefix = 'app_';

	public $prepared = FALSE;

	private $db = '';
	private $stmt = '';

	protected $table = '';
	protected $com = ' AND ';
	protected $after_field = '';

	protected $sql = ['select' => '*',
					'table' => '',
					'leftJoin' => '',
					'rightjoin' => '',
					'where' => '',
					'group' => '',
					'order' => '',
					'limit' => ''];

	protected $where_param = [];
	protected $bind_param = [];

	public function __construct($str = null)
	{
		$this->setDBConfig();
		if($this->db == '')
		{
			try{
				$this->db = new PDO("mysql:host=$this->db_host;dbname=$this->db_databases","$this->db_username","$this->db_password",$this->_opts_values);
			}catch(Exception $e) {
				die('mysql contect fail');
			}
		}

	}


	/**
	 * [count]
	 *
	 * @return [int] [The number of eligible data]
	 *         [Boolean]
	 */
	public function count()
	{
		$this->sql['select'] = 'COUNT(*)';

		$sql = 'SELECT '.implode(' ', $this->sql);

		$this->stmt = $this->db->prepare($sql);

		$this->bindParam($this->bind_param);

		$result = $this->query($this->where_param);

		if($result['flag'])
			return (int)($result['data'][0]['COUNT(*)']) ?? FALSE;
		else
			return FALSE;

	}


	/**
	 * [Get a single field]
	 *
	 * @return [type] [The field with you need]
	 */
	public function findIt()
	{
		$this->sql['limit'] = 'LIMIT 1';

		$sql = 'SELECT '.implode(' ', $this->sql);

		$this->stmt = $this->db->prepare($sql);

		$this->bindParam($this->bind_param);

		$result = $this->query($this->where_param);

		if($result['flag'])
			return $result['data'][0][$this->after_field] ?? FALSE;
		else
			return FALSE;
	}


	/**
	 * [Get a row data]
	 *
	 * @return [array]
	 *         		[boolean] [flag]
	 *         	 	[string]  [message]
	 *         	 	[string]  [sql]
	 */
	public function find()
	{
		$this->sql['limit'] = 'LIMIT 1';

		$sql = 'SELECT '.implode(' ', $this->sql);

		$this->stmt = $this->db->prepare($sql);

		$this->bindParam($this->bind_param);

		$result = $this->query($this->where_param);

		if($result['flag'])
			$result['data'] = $result['data'][0];

		return $result;
	}

	/**
	 * [Get data]
	 *
	 * @return [array]
		 *          [boolean] 	[flag]
		 *         	[array]  	[data]
		 *         	[string]  	[message]
		 *         	[string]  	[sql]
	 */
	public function get()
	{
		$sql = 'SELECT '.implode(' ', $this->sql);

		$this->stmt = $this->db->prepare($sql);

		$this->bindParam($this->bind_param);

		return $this->query($this->where_param);
	}

	/**
	 * [Insert]
	 * @param  [type] $data [description]
	 *
	 * @return [array]
	 *          	[boolean] 	[flag]
	 *         	 	[int]  		[the number of include rows]
	 *         	 	[string]  	[message]
	 *         	  	[string]  	[sql]
	 */
	public function insert($data)
	{
		if(!is_array($data)) return false;

		if(isset($data[0]))
		{
			foreach ($data[0] as $key => $value) {
				$name[] = $key;
				$values[] = ':'.$key;
			}
		}else{
			foreach ($data as $key => $value) {
				$name[] = $key;
				$values[] = ':'.$key;
			}
		}

		$sql = 'INSERT INTO '. $this->table . ' (' .implode(",", $name).')' . ' VALUES(' . implode(",", $values) .')';

		$this->stmt = $this->db->prepare($sql);

		$this->bindParam($name);

		if(isset($data[0]))
		{
			foreach ($data as $key => $value) {
				$res = $this->exec($value);
			}
		}else{
			return $this->exec($data);
		}

	}


	/**
	 * [update]
	 * @param  [type] $data [description]
	 *
	 * @return [array]
	 *         		[boole]   flag    [flag]
	 *         		[int]     data    [the number of include rows]
	 */
	public function update($data)
	{
		if(!is_array($data)) return false;

		foreach ($data as $key => $value) {
			$update[] = $key . ' = :' . $key;
		}

		$sql = 'UPDATE '. $this->table . ' SET '. implode(",", $update). $this->sql["where"];

		$this->stmt = $this->db->prepare($sql);

		$this->bindParam($update);

		//where_param与更新数据合并
		if(!empty($this->where_param))
			$data = array_merge($data, $this->where_param);

		return $this->exec($data);
	}


	public function delete()
	{
		$this->sql['select'] = '';

		$sql = 'DELETE '.implode(' ', $this->sql);

		$this->stmt = $this->db->prepare($sql);

		$this->bindParam($this->bind_param);

		return $this->exec($this->where_param);
	}

	/**
	 * [leftJoin]
	 * @param  [string] $table  [table]
	 * @param  [string] $field1 [relevance table]
	 * @param  [string] $field2 [main table]
	 *
	 * @return [object] [itself]
	 */
	public function leftJoin($table, $field1, $field2)
	{
		$this->sql['leftJoin'] = 'left join '.$this->prefix.$table.' on '.$field1.' = '.$field2;
		return $this;
	}

	public function table($table)
	{
		$this->sql['table'] = ' FROM '. $this->prefix.$table;
		$this->table = $this->prefix.$table;
		return $this;
	}

	/**
	 * [where description]
	 * @param  [string|array] $where [description]
	 * @example where('id = 1')    暂不支持
	 * @example where('id', 1)
	 * @example where('id', '=', 1)
	 * @example where($data)	[array]
	 * @return [object] [itself]
	 */
	public function where($where)
	{
		if(is_array($where))
		{
			foreach ($where as $key => $value)
			{
				if(is_array($value))
				{
					if(in_array($value[0], ['like', 'LIKE']))
						$value[1] = '%'.$value[1].'%';

					$this->where_param[$key] = $value[1];
					$this->bind_param[] = $key;

					$arr[] = $key .' '. $value[0]. ' :'. $key;
				}else{

					$this->where_param[$key] = $value;
					$this->bind_param[] = $key;

					$arr[] = $key . ' = :'. $key;
				}
			}

			if(empty($this->sql['where']))
				$this->sql['where'] = ' WHERE '. implode($this->com, $arr);
			else
				$this->sql['where'] .= $this->com. implode($this->com, $arr);
		}
		else{
			$count = func_num_args();
			$args = func_get_args();

			if($count > 1)
			{
				if($count == 3){
					if(empty($this->sql['where'])) {

						$this->where_param[$args[0]] = $args[2];
						$this->bind_param[] = $args[0];

						$this->sql['where'] = ' WHERE '.$args[0].' '.$args[1].' :'.$args[0];

					}
					else {

						$this->where_param[$args[0]] = $args[2];
						$this->bind_param[] = $args[0];

						$this->sql['where'] .= $this->com.$args[0].' '.$args[1].' :'.$args[0];
					}

				}else{
					if(empty($this->sql['where'])) {

						$this->where_param[$args[0]] = $args[1];
						$this->bind_param[] = $args[0];

						$this->sql['where'] = ' WHERE '.$args[0].' = :'.$args[0];
					}
					else {

						$this->where_param[$args[0]] = $args[1];
						$this->bind_param[] = $args[0];

						$this->sql['where'] .= $this->com.$args[0].' = :'.$args[0];
					}
				}
			}
		}
		return $this;
	}

	public function orWhere($where)
	{
		$this->com = ' OR ';
		$this->where($where);
		$this->com = ' AND ';

		return $this;
	}

	public function group($field)
	{
		$this->sql['group'] = ' GROUP BY '.$field;

		return $this;
	}

	public function order($field, $method = 'ASC')
	{
		$this->sql['order'] = ' ORDER BY '.$field. ' '. $method;

		return $this;
	}

	public function limit($start, $num = '')
	{
		if($num == '')
		{
			$this->sql['limit'] = 'LIMIT '.$start;
		}else{
			$this->sql['limit'] = 'LIMIT '.$start.' '.$num;
		}

		return $this;
	}

	public function select()
	{
		$args = func_get_args();
		if(is_array($args[0]))
		{
			$this->sql['select'] = implode(',', $args[0]);
		}else{
			$this->sql['select'] = implode(',', $args);
		}

		return $this;
	}

	protected function query($data)
	{
		try {
			$info = $this->stmt->execute($data);
		} catch (Exception $e) {
			$result['flag'] = FALSE;
			$result['msg'] = $e->errorInfo[2];
			$result['sql'] = $this->stmt->queryString;
			return $result;
		}

		while ($row[] = $this->stmt->fetch(PDO::FETCH_ASSOC)){}
		array_pop($row);

		$this->after_field = $this->sql['select'];

		$this->init();

		$result['flag'] = TRUE;
		$result['data'] = $row;

		return $result;
	}

	protected function exec($data)
	{
		try {
			$info = $this->stmt->execute($data);
		} catch (Exception $e) {
			$result['flag'] = FALSE;
			$result['msg'] = $e->errorInfo[2] ?? null;
			$result['sql'] = $this->stmt->queryString;
			return $result;
		}

		$this->init();

		$result['flag'] = TRUE;
		$result['data'] = $this->stmt->rowCount();
		return $result;
	}

	//绑定
	protected function bindParam($key)
	{
		foreach ($key as $val) {
			$this->stmt->bindParam(':'.$val, $val);
		}
	}

	protected function init()
	{
		$this->sql = ['select' => '*',
					'table' => '',
					'leftJoin' => '',
					'rightjoin' => '',
					'where' => '',
					'group' => '',
					'order' => '',
					'limit' => ''];
	}

	protected function setDBConfig()
	{
		$config = require_once("db_config.php");

		$this->db_host = $config['DB_HOST'];
		$this->db_username = $config['DB_USERNAME'];
		$this->db_password = $config['DB_PASSWORD'];
		$this->db_databases = $config['DB_DATABASE'];
	}
}