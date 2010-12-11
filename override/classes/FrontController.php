<?php
/*
* 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

class FrontController extends FrontControllerCore
{
	public $_memory = array();
	public $_time = array();
	
	private function displayMemoryColor($n)
	{
		$n /= 1048576;
		if ($n > 3)
			return '<span style="color:red">'.round($n, 2).' Mb</span>';
		if ($n > 1)
			return '<span style="color:orange">'.round($n, 2).' Mb</span>';
		return '<span style="color:green">'.round($n, 2).' Mb</span>';
	}
	
	private function displaySQLQueries($n)
	{
		if ($n > 150)
			return '<span style="color:red">'.$n.' queries</span>';
		if ($n > 100)
			return '<span style="color:orange">'.$n.' queries</span>';
		return '<span style="color:green">'.$n.' quer'.($n == 1 ? 'y' : 'ies').'</span>';
	}
	
	private function displayLoadTimeColor($n, $kikoo = false)
	{
		if ($n > 1)
			return '<span style="color:red">'.round($n, 3).'s</span>'.($kikoo ? ' - You\'d better run your shop on a toaster' : '');
		if ($n > 0.5)
			return '<span style="color:orange">'.round($n, 3).'s</span>'.($kikoo ? ' - Alright if you\'re on a shared hosting platform' : '');
		return '<span style="color:green">'.round($n, 3).'s</span>'.($kikoo ? ' - Good boy! That\'s what I call a webserver!' : '');
	}
	
	private function getTimeColor($n)
	{
		if ($n > 4)
			return 'style="color:red"';
		if ($n > 2)
			return 'style="color:orange"';
		return 'style="color:green"';
	}
	
	private function getQueryColor($n)
	{
		if ($n > 5)
			return 'style="color:red"';
		if ($n > 2)
			return 'style="color:orange"';
		return 'style="color:green"';
	}
	
	private function getTableColor($n)
	{
		if ($n > 30)
			return 'style="color:red"';
		if ($n > 20)
			return 'style="color:orange"';
		return 'style="color:green"';
	}
	
	public function __construct()
	{
		$this->_memory[-2] = memory_get_usage();
		$this->_time[-2] = microtime(true);
		parent::__construct();
		$this->_memory[-1] = memory_get_usage();
		$this->_time[-1] = microtime(true);
	}
	
	public function run()
	{
		$this->_memory[0] = memory_get_usage();
		$this->_time[0] = microtime(true);
		$this->preProcess();
		$this->_memory[1] = memory_get_usage();
		$this->_time[1] = microtime(true);
		$this->setMedia();
		$this->_memory[2] = memory_get_usage();
		$this->_time[2] = microtime(true);
		$this->displayHeader();
		$this->_memory[3] = memory_get_usage();
		$this->_time[3] = microtime(true);
		$this->process();
		$this->_memory[4] = memory_get_usage();
		$this->_time[4] = microtime(true);
		$this->displayContent();
		$this->_memory[5] = memory_get_usage();
		$this->_time[5] = microtime(true);
		$this->displayFooter();
	}
	
	
	public function displayFooter()
	{
		global $start_time;
		parent::displayFooter();
		
		$this->_memory[6] = memory_get_usage();
		$this->_time[6] = microtime(true);
		
		$hr = '<hr style="color:#F5F5F5;margin:2px" />';

		$totalSize = 0;
		foreach (get_included_files() as $file)
			$totalSize += filesize($file);

		echo '<br /><br />
		<div class="rte" style="text-align:left;padding:8px;float:left">
			<b>Load time</b>: '.$this->displayLoadTimeColor($this->_time[6] - $start_time, true).'
			<ul>
				<li>Config: '.$this->displayLoadTimeColor($this->_time[-2] - $start_time).'</li>
				<li>Constructor: '.$this->displayLoadTimeColor(($this->_time[-1] - $this->_time[-2])).'</li>
				<li>preProcess: '.$this->displayLoadTimeColor(($this->_time[1] - $this->_time[0])).'</li>
				<li>setMedia: '.$this->displayLoadTimeColor(($this->_time[2] - $this->_time[1])).'</li>
				<li>displayHeader: '.$this->displayLoadTimeColor(($this->_time[3] - $this->_time[2])).'</li>
				<li>process: '.$this->displayLoadTimeColor(($this->_time[4] - $this->_time[3])).'</li>
				<li>displayContent: '.$this->displayLoadTimeColor(($this->_time[5] - $this->_time[4])).'</li>
				<li>displayFooter: '.$this->displayLoadTimeColor(($this->_time[6] - $this->_time[5])).'</li>
			</ul>
		</div>
		<div class="rte" style="text-align:left;padding:8px;float:left;margin-left:20px">
			<b>Memory usage</b>: '.$this->displayMemoryColor($this->_memory[6]).', including '.$this->displayMemoryColor($totalSize).' of files
			<ul>
				<li>Config: '.$this->displayMemoryColor($this->_memory[-2]).'</li>
				<li>Constructor: '.$this->displayMemoryColor(($this->_memory[-1] - $this->_memory[-2])).'</li>
				<li>preProcess: '.$this->displayMemoryColor(($this->_memory[1] - $this->_memory[0])).'</li>
				<li>setMedia: '.$this->displayMemoryColor(($this->_memory[2] - $this->_memory[1])).'</li>
				<li>displayHeader: '.$this->displayMemoryColor(($this->_memory[3] - $this->_memory[2])).'</li>
				<li>process: '.$this->displayMemoryColor(($this->_memory[4] - $this->_memory[3])).'</li>
				<li>displayContent: '.$this->displayMemoryColor(($this->_memory[5] - $this->_memory[4])).'</li>
				<li>displayFooter: '.$this->displayMemoryColor(($this->_memory[6] - $this->_memory[5])).'</li>
			</ul>
		</div>';
		
		$countByTypes = '';
		foreach (Db::getInstance()->countTypes as $type => $count)
			if ($count)
				$countByTypes .= '<li>'.$count.' x '.$type.'</li>';
		$countByTypes = rtrim($countByTypes, ' |');
		
		echo '
		<div class="rte" style="text-align:left;padding:8px;float:left;margin-left:20px">
			<b>SQL Queries</b>: '.$this->displaySQLQueries(Db::getInstance()->count).'
			<ul>'.$countByTypes.'</ul>
		</div>
		<div class="rte" style="text-align:left;padding:8px;clear:both;margin-top:20px">
			<ul>
				<li><a href="#stopwatch">Go to Stopwatch</a></li>
				<li><a href="#doubles">Go to Doubles</a></li>
				<li><a href="#tables">Go to Tables</a></li>
			</ul>
		</div>
		<div class="rte" style="text-align:left;padding:8px">
		<h3><a name="stopwatch">Stopwatch (with SQL_NO_CACHE)</a></h3>';
		$queries = Db::getInstance()->queriesTime;
		arsort($queries);
		foreach ($queries as $q => $time)
			echo $hr.'<b '.$this->getTimeColor($time * 1000).'>'.round($time * 1000, 3).' ms</b> '.$q;
		echo '</div>
		<div class="rte" style="text-align:left;padding:8px">
		<h3><a name="doubles">Doubles (IDs replaced by "XX")</a></h3>';
		$queries = Db::getInstance()->queries;
		arsort($queries);
		foreach ($queries as $q => $nb)
			echo $hr.'<b '.$this->getQueryColor($nb).'>'.$nb.'</b> '.$q;
		echo '</div>
		<div class="rte" style="text-align:left;padding:8px">
		<h3><a name="tables">Tables stress</a></h3>';
		$tables = Db::getInstance()->tables;
		arsort($tables);
		foreach ($tables as $table => $nb)
			echo $hr.'<b '.$this->getTableColor($nb).'>'.$nb.'</b> '.$table;
		echo '</div>';
	}
}
