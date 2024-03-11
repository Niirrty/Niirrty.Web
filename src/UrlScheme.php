<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2016-2021, Ni Irrty
 * @package        Niirrty\Web
 * @since          2017-11-02
 * @subpackage     …
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\Web;


interface UrlScheme
{


    /**
     * The 'http' scheme.
     */
    const string HTTP = 'http';

    /**
     * The 'https' SSL scheme.
     */
    const string SSL = 'https';

    /**
     * The 'ftp' scheme.
     */
    const string FTP = 'ftp';


}

