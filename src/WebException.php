<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2016-2021, Ni Irrty
 * @package        Niirrty\Web
 * @since          2017-11-02
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\Web;


use \Niirrty\NiirrtyException;


/**
 * This class defines a exception, used as base exception of all web exceptions.
 *
 * It extends from {@see \Niirrty\MessierException}.
 */
class WebException extends NiirrtyException
{


    #region // – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –

    /**
     * Init a new instance.
     *
     * @param string          $message  The error message.
     * @param integer         $code     The optional error code (Defaults to \E_USER_ERROR)
     * @param \Throwable|null $previous A optional previous exception
     */
    public function __construct( $message, int $code = 256, ?\Throwable $previous = null )
    {

        parent::__construct(
            $message,
            $code,
            $previous
        );

    }

    #endregion


}

