<?php

/**
*  The maximum identifier length is necessary because of database identifier
*  limitations. Oracle allows 30 characters, MySql allows 64, etc. We also
*  want to garantee some space for prefixes/suffixes when generating
*  some entity related tables, constraints, etc. Set value according to
*  database(s) you want to support.
*/
define('MAX_IDENTIFIER_LENGTH', 25);


/**
*  If language is not specified in <label> tags this is the one we assume.
*/
define('DEFAULT_LANG','en');
