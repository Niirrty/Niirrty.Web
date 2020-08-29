<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2016-2020, Ni Irrty
 * @package        Niirrty\Web
 * @since          2017-11-02
 * @subpackage     …
 * @version        0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Web;


interface UrlScheme
{


    /**
     * The 'http' scheme.
     */
    const HTTP = 'http';

    /**
     * The 'https' SSL scheme.
     */
    const SSL = 'https';

    /**
     * The 'ftp' scheme.
     */
    const FTP = 'ftp';


}

