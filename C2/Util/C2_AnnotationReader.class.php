<?php

final class C2_AnnotationReader
{
    public static function getAnnotations($comment)
    {
        $comments = explode(PHP_EOL, $comment);
        if (empty($docComment)) {
            return array();
        }
        $annos = array();
        foreach ($docComment as &$line) {
            $line = trim($line);
            $line = trim(preg_replace(array(
            		'/^\/\*\*/',
            		'/\*\/$/',
            		'/^\*/'
            ), '', $line));
            if ($line == "") {
                continue;
            }
            if (preg_match('/^@(\w+)((\s+\w+)*)$/', $line, $matches) > 0) {
                if (isset($matches[2])) {
                    $annoArgs = explode(' ', trim($matches[2]));
                }
                array_push($annos, array('command' => $matches[1], 'args' => $annoArgs));
            }
        }
        return $annos;
    }
    
    /**
     * Get annotation command arguments.
     * 
     * ex.) "@exampleCommand arg1 arg2" => array('arg1', 'arg2')
     * 
     * @param string $comment docComment
     * @param string $commandName annotation command name which you want to get arguments.
     */
    public static function getAnnotationArgs($comment, $commandName)
    {
    	if (empty($commandName)) {
    		return null;
    	}
    	$annos = self::getAnnotations($comment);
		foreach ($annos as $anno) {
			if ($anno['command'] == $commandName) {
				return $anno['args'];
			}
		}
		return null;
    }
}