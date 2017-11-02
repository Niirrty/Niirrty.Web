<?php


declare( strict_types = 1 );


namespace Niirrty\Web;


use Mso\IdnaConvert\IdnaConvert;


function idnToASCII( ?string $str ) : string
{

   if ( null === $str ) { return ''; }

   if ( \function_exists( '\\idn_to_ascii' ) )
   {
      return \idn_to_ascii( $str );

   }

   return ( new IdnaConvert() )->encode( $str );

}

/**
 * Checks if the defined string value uses a valid IPv4 address format
 *
 * @param string $value
 * @return bool
 */
function isIPv4Address( string $value ) : bool
{

   return (bool) \preg_match(
      "~^(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){3}$~",
      $value
   );

}

/**
 * Checks if the defined string value uses a valid IPv6 address format
 *
 * @param string $value
 * @return bool
 */
function isIPv6Address( string $value ) : bool
{

   return (bool) \preg_match(
      "~^([0-9a-fA-F]{1,4}(:[0-9a-fA-F]{1,4}){7}|::[0-9a-fA-F]{1,4}([0-9a-fA-F:.]+)?(/\d{1,3})?|::[0-9a-fA-F]{0,4})(/\d{1,3})?$~",
      $value
   );

}

/**
 * Checks if the defined string value uses a valid IPv4 or IPv6 address format
 *
 * @param string $value
 * @return bool
 */
function isIPAddress( string $value ) : bool
{

   return isIPv4Address( $value ) || isIPv6Address( $value );

}

