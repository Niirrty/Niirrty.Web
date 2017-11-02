<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2016, Ni Irrty
 * @package        Messier.DBLib
 * @since          2017-11-02
 * @subpackage     …
 * @version        0.1.0
 */


declare( strict_types = 1 );


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
   const SSL  = 'https';

   /**
    * The 'ftp' scheme.
    */
   const FTP  = 'ftp';


}

