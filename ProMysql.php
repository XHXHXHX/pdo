<?php

class ProMysql{

	private $db_host = '';
	private $db_username = '';
	private $db_password = '';
	private $db_databases = '';

	private $_opts_values=array(PDO::ATTR_PERSISTENT=>true,PDO::ATTR_ERRMODE=>2,PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES utf8');

	public $prefix = 'app_';

	private $db = '';

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

	public function findIt()
	{
		$this->sql['limit'] = 'LIMIT 1';

		$sql = 'SELECT '.implode(' ', $this->sql);

		$result = $this->query($sql);

		if($result['flag'])
			return $result['data'][0][$this->after_field] ?? null;
		else
			return FALSE;
	}

	/**
	 * [Find It!!!!]
	 * @return [string] [It]
	 */
	public function find()
	{
		$this->sql['limit'] = 'LIMIT 1';

		$sql = 'SELECT '.implode(' ', $this->sql);

		$result = $this->query($sql);

		if($result['flag'])
			$result['data'] = $result['data'][0];

		return $result;
	}

	public function get()
	{
		$sql = 'SELECT '.implode(' ', $this->sql);

		return $this->query($sql);
	}

	public function insert($data)
	{
		if(!is_array($data)) return false;

		foreach ($data as $key => $value) {
			$name[] = $key;
			$values[] = '"'.$value.'"';
		}

		$sql = 'INSERT INTO '. $this->table . ' (' .implode(",", $name).')' . ' VALUES(' . implode(",", $values) .')';

		return $this->exec($sql);
	}

	/**
	 * [update description]
	 * @param  [type] $data [description]
	 * @return [array]
	 *         		[boole]   flag    [flag]
	 *         		[int]     data    [include number of rows]
	 */
	public function update($data)
	{
		if(!is_array($data)) return false;

		foreach ($data as $key => $value) {
			$update[] = $key . ' = "' . $value . '"';
		}

		$sql = 'UPDATE '. $this->table . ' SET '. implode(",", $update). $this->sql["where"];
		return $this->exec($sql);
	}

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
	 * @example where('id = 1')
	 * @example where('id', 1)
	 * @example where('id', '=', 1)
	 * @example where($data)	[array]
	 * @return [object]    self    [itself]
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
					$arr[] = $key .' '. $value[0]. ' "'. $value[1].'"';
				}else{
					$arr[] = $key . ' = '. $value;
				}
			}

			$this->sql['where'] = ' WHERE '. implode($this->com, $arr);
		}
		else{
			$count = func_num_args();
			$args = func_get_args();

			if($count > 1)
			{
				if($count == 3){
					$this->sql['where'] = ' WHERE '.$args[0].' '.$args[1].' "'.$args[2].'"';
				}else{
					$this->sql['where'] = ' WHERE '.$args[0].' = "'.$args[1].'"';

				}
			}else{
				$this->sql['where'] = ' WHERE '.$where;
			}
		}
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

	protected function query($sql)
	{
		try {
			$info = $this->db->query($sql);
		} catch (Exception $e) {
			$result['flag'] = FALSE;
			$result['msg'] = $e->errorInfo[2];
			$result['sql'] = $sql;
			return $result;
		}

		while ($row[] = $info->fetch(PDO::FETCH_ASSOC)){}
		array_pop($row);

		$this->after_field = $this->sql['select'];

		$this->init();

		$result['flag'] = TRUE;
		$result['data'] = $row;

		return $result;
	}

	protected function exec($sql)
	{
		try {
			$info = $this->db->exec($sql);
		} catch (Exception $e) {
			$result['flag'] = FALSE;
			$result['msg'] = $e->errorInfo[2];
			$result['sql'] = $sql;
			return $result;
		}

		$this->init();

		$result['flag'] = TRUE;
		$result['data'] = $info;
		return $result;
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