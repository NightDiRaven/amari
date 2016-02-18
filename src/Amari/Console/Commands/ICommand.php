<?php
/**
 * Created by PhpStorm.
 * User: IN
 * Date: 16.02.2016
 * Time: 18:54
 */

namespace Amari\Console\Commands;


interface ICommand {
	/**
	 * Return command name
	 *
	 * @return String
	 */
	public function title();

	/**
	 * Return lists of all subcommands
	 *
	 * @return Array
	 */
	public function lists();

	/**
	 * Check existing of subcommand
	 *
	 * @return Array
	 */
	public function exists($subCommand);

	/**
	 * Execute command
	 *
	 * @return Array
	 */
	public function doCommand($subCommand);

	/**
	 * Default command if subcommand not exists
	 *
	 * @return Array
	 */
	public function index($subCommand);
}