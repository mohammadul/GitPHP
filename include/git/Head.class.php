<?php
/**
 * GitPHP Head
 *
 * Represents a single head
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 * @subpackage Git
 */

/**
 * Head class
 *
 * @package GitPHP
 * @subpackage Git
 */
class GitPHP_Head extends GitPHP_Ref
{

	/**
	 * __construct
	 *
	 * Instantiates head
	 *
	 * @access public
	 * @param mixed $project the project
	 * @param string $head head name
	 * @param string $headHash head hash
	 * @return mixed head object
	 * @throws Exception exception on invalid head or hash
	 */
	public function __construct($project, $head, $headHash = '', $refDir='heads')
	{
		parent::__construct($project, $refDir, $head, $headHash);
	}

	/**
	 * GetCommit
	 *
	 * Gets the commit for this head
	 *
	 * @access public
	 * @return mixed commit object for this tag
	 */
	public function GetCommit()
	{
		return $this->GetProject()->GetCommit($this->GetHash());
	}
	
	/**
	 * CompareAge
	 *
	 * Compares two heads by age
	 *
	 * @access public
	 * @static
	 * @param mixed $a first head
	 * @param mixed $b second head
	 * @return integer comparison result
	 */
	public static function CompareAge($a, $b)
	{
		$aObj = $a->GetCommit();
		$bObj = $b->GetCommit();
		return GitPHP_Commit::CompareAge($aObj, $bObj);
	}

	/**
	 * Generates a head hash key
	 *
	 * @param mixed $proj project
	 * @param string $head head name
	 * @return string cache key
	 */
	public static function CacheKey($proj, $head)
	{
		if (is_object($proj))
			return 'project|' . $proj->GetProject . '|head|' . $head;

		return 'project|' . $proj . '|head|' . $head;
	}

}
