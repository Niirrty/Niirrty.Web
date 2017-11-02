<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2016, Ni Irrty
 * @package        Niirrty\Web
 * @since          2017-11-02
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Web;


use \Niirrty\ArrayHelper;


/**
 * This class defines a second level domain (SLD). Its defined by the second-level-domain-label and a optional
 * top-level-domain-label. If the TLD label is defined it must be separated from SLD label by a dot. If the TLD
 * is defined it can be defined as a fully qualified TLD
 *
 * <code>
 * third-level-domain-label  .  second-level-domain-label  .  top-level-domain-label  .  root-label
 * www                       .  example                    .  com
 * </code>
 *
 * The second level domain from example above is <b>example.com</b> or <b>example.com.</b> (last is fully qualified)
 *
 * For some validation reasons the class can get &amp; store some testing states, informing about
 * SecondLevelDomain details.
 *
 * All this states can be accessed via associated is*() methods.
 */
class SecondLevelDomain
{


   // <editor-fold desc="// – – –   P R I V A T E   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * The host name element string.
    *
    * @var string
    */
   private $_hostName;

   /**
    * The TopLevelDomain element.
    *
    * @var \Niirrty\Web\TopLevelDomain
    */
   private $_tld;

   /**
    * A array of states (Detail Information about the Domain)
    *
    * @var array
    */
   private $states;

   // </editor-fold>


   // <editor-fold desc="// – – –   C L A S S   C O N S T A N T S   – – – – – – – – – – – – – – – – – – – – – – –">

   protected const URL_SHORTENERS   = [
      'bit.do', 't.co', 'lnkd.in', 'db.tt', 'qr.ae', 'adf.ly', 'goo.gl', 'bitly.com', 'cur.lv', 'tinyurl.com',
      'ow.ly', 'bit.ly', 'adcrun.ch', 'ity.im', 'q.gs', 'viralurl.com', 'is.gd', 'vur.me', 'bc.vc', 'twitthis.com',
      'u.to', 'j.mp', 'buzurl.com', 'cutt.us', 'u.bb', 'yourls.org', 'crisco.com', 'x.co', 'prettylinkpro.com',
      'viralurl.biz', 'adcraft.co', 'virl.ws', 'scrnch.me', 'filoops.info', 'vurl.bz', 'vzturl.com', 'lemde.fr',
      'qr.net', '1url.com', 'tweez.me', '7vd.cn', 'v.gd', 'dft.ba', 'aka.gr', 'tr.im', 'tinyarrows.com',
      'adflav.com', 'bee4.biz', 'cektkp.com', 'fun.ly', 'fzy.co', 'gog.li', 'golinks.co', 'hit.my', 'id.tl',
      'linkto.im', 'lnk.co', 'nov.io', 'p6l.org', 'picz.us', 'shortquik.com', 'su.pr', 'sk.gy', 'tota2.com',
      'xlinkz.info', 'xtu.me', 'yu2.it', 'zpag.es'
   ];

   // see: http://dnslookup.me/dynamic-dns/
   protected const DYN_DNS_SERVICES = '~^(.+\.wow64|(cable|optus|ddns|evangelion)\.nu|(45z|au2000|user32|darsite|darweb|dns2go|dnsmadeeasy|dnspark|dumb1|dyn(dns|dsl|serv|-access|nip)|thatip|tklapp|weedns|easydns|tzo|easydns4u|etowns|freelancedeveloper|hldns|powerdns|kyed|no-ip|ohflip|oray|servequake|usarmyreserve|wikababa|zerigo|zoneedit|zonomi)\.com|(dtdns|dynamic-dns|dynamic-site|dyns|dynserv|dynup|dyn-access|idleplay|minidns|sytes|tftpd|cjb|8866|xicp|planetdns|tzo)\.net|(afraid|3322|darktech|dhis|dhs|dynserv|dyn-access|irc-chat|planetdns|tzo)\.org|(dnsd|prout)\.be|dyn\.ee|dyn-access\.(de|info|biz)|dynam\.ac|dyn\.ro|my-ho\.st|(dyndns|lir|yaboo)\.dk|(dyns|metadns)\.cx|(homepc|myserver|ods|staticcling|yi|whyi|b0b|xname)\.org|widescreenhd\.tv|planetdns\.(biz|ca)|tzo\.cc)$~i';

   protected const LOCAL_HOSTS      = '~^(local(host|domain)?)$~';

   protected const RESERVED_HOSTS   = '~^(example\.(com|net|org)|speedport\.ip|router\.net)$~';

   // </editor-fold>


   // <editor-fold desc="// – – –   P R I V A T E   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – –">

   private function __construct( $hostname, ?TopLevelDomain $tld = null )
   {

      $this->_hostName = $hostname;

      $this->_tld = $tld;

      $this->states = [ 'RESERVED' => false, 'LOCAL' => false, 'SHORTENER' => false, 'DYNAMIC' => false ];

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * Returns the the SecondLevelDomain string value of this instance. If its defined as a fully qualified SLD
    * (it must ends with a dot) its returned with the trailing dot, otherwise without it.
    *
    * @return string
    */
   public function __toString()
   {

      if ( empty( $this->_hostName ) )
      {
         if ( null === $this->_tld )
         {
            return '';
         }
         return (string) $this->_tld;
      }

      if ( null === $this->_tld )
      {
         return $this->_hostName;
      }

      return $this->_hostName . '.' . $this->_tld;

   }

   /**
    * Returns always the fully qualified SLD. A fully qualified SLD always ends with a dot (like 'example.com.')
    *
    * @return string
    */
   public function toFullyQualifiedString()
   {

      if ( empty( $this->_hostName ) )
      {
         return $this->_tld->toFullyQualifiedString();
      }

      if ( null === $this->_tld )
      {
         return $this->_hostName;
      }

      return $this->_hostName . '.' . $this->_tld->toFullyQualifiedString();

   }

   /**
    * Returns always the NOT fully qualified SLD, also if a fully qualified SLD is used.
    *
    * @return string
    */
   public function toString()
   {

      if ( empty( $this->_hostName ) )
      {
         return $this->_tld->toString();
      }

      if ( null === $this->_tld )
      {
         return $this->_hostName;
      }

      return $this->_hostName . '.' . $this->_tld->toString();

   }

   /**
    * Gets the TLD part or NULL if no TLD is defined
    *
    * @return \Niirrty\Web\TopLevelDomain|null
    */
   public function getTLD() : ?TopLevelDomain
   {

      return $this->_tld;

   }

   /**
    * Sets a new TLD part.
    *
    * @param \Niirrty\Web\TopLevelDomain|null $tld
    * @return \Niirrty\Web\SecondLevelDomain
    */
   public function setTLD( ?TopLevelDomain $tld ) : SecondLevelDomain
   {

      $this->_tld = $tld;

      return $this;

   }

   /**
    * Gets if a TLD is defined.
    *
    * @return bool
    */
   public function hasTLD() : bool
   {

      return ( null !== $this->_tld );

   }

   /**
    * Gets the host name part of the second level domain.
    *
    * @return null|string
    */
   public function getHostName() : ?string
   {

      return $this->_hostName;

   }

   /**
    * Gets if the instance declares a fully qualified domain.
    *
    * @return bool
    */
   public function isFullyQualified() : bool
   {

      if ( ! $this->hasTLD() ) { return false; }

      return $this->_tld->isFullyQualified();

   }

   /**
    * Is the current TLD value (if defined) a known COUNTRY TLD? Country TopLevelDomains are: 'cz', 'de', etc. and
    * also localized country TopLevelDomains (xn--…).
    *
    * @return bool
    */
   public function isCountry() : bool
   {

      if ( ! $this->hasTLD() ) { return false; }

      return $this->_tld->isCountry();


   }

   /**
    * Is the current TLD value (if defined) a known GENERIC TLD? Generic TopLevelDomains are: 'com', 'edu', 'gov',
    * 'int', 'mil', 'net', 'org' and also the associated localized TopLevelDomains (xn--…).
    *
    * @return bool
    */
   public function isGeneric() : bool
   {

      if ( ! $this->hasTLD() ) { return false; }

      return $this->_tld->isGeneric();

   }

   /**
    * Is the current TLD value (if defined) a GEOGRAPHIC TLD? Geographic TLDs are: 'asia', 'berlin', 'london', etc.
    *
    * @return bool
    */
   public function isGeographic() : bool
   {

      if ( ! $this->hasTLD() ) { return false; }

      return $this->_tld->isGeographic();

   }

   /**
    * Is the current TLD (if defined) a known LOCALIZED UNICODE TLD? Localized TLDs are starting with 'xn--'.
    *
    * @return bool
    */
   public function isLocalized() : bool
   {

      if ( ! $this->hasTLD() ) { return false; }

      return $this->_tld->isLocalized();

   }

   /**
    * Is the current TLD or SLD value a known RESERVED name? Reserved TopLevelDomains are 'arpa', 'test', 'example'
    * and 'tld'. Reserved SLDs are example.(com|net|org)
    *
    * @return bool
    */
   public function isReserved() : bool
   {

      if ( $this->states[ 'RESERVED' ] ) { return true; }

      if ( ! $this->hasTLD() ) { return false; }

      return $this->_tld->isReserved();

   }

   /**
    * Returns if the TLD is a generally known, registered TLD!
    *
    * @return bool
    */
   public function hasKnownTLD() : bool
   {

      if ( ! $this->hasTLD() ) { return false; }

      return $this->_tld->isKnown();

   }

   /**
    * Is the SLD (Hostname or TLD or both) representing a local SLD(-Element)?
    *
    * @return bool
    */
   public function isLocal() : bool
   {

      return $this->states[ 'LOCAL' ];

   }

   /**
    * Returns if the TLD is a known double TLD like co.uk.
    *
    * @return bool
    */
   public function hasDoubleTLD() : bool
   {

      if ( ! $this->hasTLD() ) { return false; }

      return $this->_tld->isDouble();

   }

   /**
    * Is the SLD pointing to a known public URL shortener service? They are used to shorten or hide some long or
    * bad URLs.
    *
    * @return bool
    */
   public function isUrlShortener() : bool
   {

      return $this->states[ 'SHORTENER' ];

   }

   /**
    * Is the SLD pointing to a known public dynamic DNS service
    *
    * @return bool
    */
   public function isDynamic() : bool
   {

      return $this->states[ 'DYNAMIC' ];

   }

   /**
    * Returns if a host name is defined.
    *
    * @return bool
    */
   public function hasHostName() : bool
   {

      return ! empty( $this->_hostName );

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   S T A T I C   M E T H O D S   – – – – – – – – – – – – – – – – –">

   /**
    * Parses the defined Second Level Domain string to a {@see \Niirrty\Web\SecondLevelDomain} instance. On error it
    * returns FALSE.
    *
    * @param  string                         $sld                The second level domain string, including the optional TLD.
    * @param  \Niirrty\Web\SecondLevelDomain $parsedSldOut       Returns the SecondLevelDomain if the method returns true
    * @param  bool                           $allowOnlyKnownTlds Are only known main TLDs allowed to be a parsed as a TLD?
    * @param  bool                           $convertUniCode     Convert unicode SLDs to Puny code? (Default = TRUE)
    * @return boolean
    */
   public static function TryParse(
      ?string $sld, &$parsedSldOut, bool $allowOnlyKnownTlds = false, bool $convertUniCode = true ) : bool
   {

      if ( $convertUniCode ) { $sld = idnToASCII( $sld ); }

      if ( empty( $sld ) || ! \is_string( $sld ) || \is_numeric( $sld ) )
      {
         // NULL values or none string values will always return FALSE
         return false;
      }

      $_sld = $sld;

      if ( false !== ( $_tld = TopLevelDomain::ParseExtract( $_sld, $allowOnlyKnownTlds, false ) ) )
      {
         $parsedSldOut = new SecondLevelDomain( '', $_tld );
         $parsedSldOut->states[ 'RESERVED' ]  = $_tld->isReserved();
         $parsedSldOut->states[ 'SHORTENER' ] = \in_array( \strtolower( $sld ), static::URL_SHORTENERS, true ) ;
      }
      else
      {
         if ( $allowOnlyKnownTlds && ! \Niirrty\strContains( $sld, '.' ) )
         {
            return false;
         }
         if ( $allowOnlyKnownTlds && ! TopLevelDomain::EndsWithValidTldString( $sld, false ) )
         {
            return false;
         }
         $parsedSldOut = new SecondLevelDomain( '' );
      }

      if ( ! \preg_match( '~^[a-z0-9_][a-z.0-9_-]+$~i', $_sld ) )
      {
         return false;
      }

      $parsedSldOut->_hostName = $_sld;

      if ( \preg_match( static::LOCAL_HOSTS, $sld ) )
      {
         $parsedSldOut->states[ 'LOCAL' ]     = true;
         $parsedSldOut->states[ 'RESERVED' ]  = true;
      }
      else if ( \preg_match( static::DYN_DNS_SERVICES, $sld ) )
      {
         $parsedSldOut->states[ 'DYNAMIC' ]  = true;
      }
      if ( ! $parsedSldOut->states[ 'RESERVED' ] && \preg_match( static::RESERVED_HOSTS, $sld ) )
      {
         $parsedSldOut->states[ 'RESERVED' ]  = true;
      }

      return true;

   }

   /**
    * Parses the defined Second Level Domain string to a {@see \Niirrty\Web\SecondLevelDomain} instance. On error it
    * returns FALSE.
    *
    * @param  string                         $sld                The second level domain string, including the optional TLD.
    * @param  bool                           $allowOnlyKnownTlds Are only known main TLDs allowed to be a parsed as a TLD?
    * @param  bool                           $convertUniCode     Convert unicode SLDs to Puny code? (Default = TRUE)
    * @return \Niirrty\Web\SecondLevelDomain|boolean
    */
   public static function Parse( ?string $sld, bool $allowOnlyKnownTlds = false, bool $convertUniCode = true )
   {

      if ( $convertUniCode ) { $sld = idnToASCII( $sld ); }

      if ( empty( $sld ) || ! \is_string( $sld ) || \is_numeric( $sld ) )
      {
         // NULL values or none string values will always return FALSE
         return false;
      }

      $_sld = $sld;

      if ( false !== ( $_tld = TopLevelDomain::ParseExtract( $_sld, $allowOnlyKnownTlds, false ) ) )
      {
         $parsedSldOut = new SecondLevelDomain( '', $_tld );
         $parsedSldOut->states[ 'RESERVED' ]  = $_tld->isReserved();
         $parsedSldOut->states[ 'SHORTENER' ] = \in_array( \strtolower( $sld ), static::URL_SHORTENERS, true ) ;
      }
      else
      {
         if ( false !== ( $_tld = TopLevelDomain::Parse( $_sld, $allowOnlyKnownTlds, false ) ) )
         {
            $parsedSldOut                        = new SecondLevelDomain( '', $_tld );
            $_sld                                = '';
            $parsedSldOut->states[ 'RESERVED' ]  = $_tld->isReserved();
            $parsedSldOut->states[ 'SHORTENER' ] = \in_array( \strtolower( $sld ), static::URL_SHORTENERS, true ) ;
         }
         else
         {
            if ( $allowOnlyKnownTlds && !\Niirrty\strContains( $sld, '.' ) )
            {
               return false;
            }
            if ( $allowOnlyKnownTlds && !TopLevelDomain::EndsWithValidTldString( $sld, false ) )
            {
               return false;
            }
            $parsedSldOut = new SecondLevelDomain( '' );
         }
      }

      if ( '' === $_sld )
      {
         return $parsedSldOut;
      }


      if ( ! \preg_match( '~^[a-z0-9_][a-z.0-9_-]+$~i', $_sld ) )
      {
         return false;
      }

      $parsedSldOut->_hostName = $_sld;

      if ( \preg_match( static::LOCAL_HOSTS, $sld ) )
      {
         $parsedSldOut->states[ 'LOCAL' ]     = true;
         $parsedSldOut->states[ 'RESERVED' ]  = true;
      }
      else if ( \preg_match( static::DYN_DNS_SERVICES, $sld ) )
      {
         $parsedSldOut->states[ 'DYNAMIC' ]  = true;
      }
      if ( ! $parsedSldOut->states[ 'RESERVED' ] && \preg_match( static::RESERVED_HOSTS, $sld ) )
      {
         $parsedSldOut->states[ 'RESERVED' ]  = true;
      }

      return $parsedSldOut;

   }

   /**
    * Extracts a Second level domain definition from a full host definition like 'www.example.com' => 'example.com'.
    * The rest (Third level label (often called 'Sub domain name')) is returned by $host, if the method returns a
    * valid {@see \Niirrty\Web\SecondLevelDomain} instance.
    *
    * @param  string  $host               The full host definition and it returns the resulting third level label
    *                                     (known as sub domain name) if the method returns a new instance
    * @param  \Niirrty\Web\SecondLevelDomain $parsedSldOut Returns the SecondLevelDomain if the method returns true
    * @param  boolean $allowOnlyKnownTlds Are only known main TLDs allowed to be a parsed as a TLD?
    * @param  bool    $convertUniCode     Convert unicode SLDs to Puny code? (Default = TRUE)
    * @return bool
    */
   public static function TryParseExtract(
      string &$host, &$parsedSldOut, bool $allowOnlyKnownTlds = false, bool $convertUniCode = false )
   {

      if ( $convertUniCode ) { $host = idnToASCII( $host ); }

      if ( empty( $host ) || ! \is_string( $host ) )
      {
         // NULL values or none string values will always return FALSE
         return false;
      }

      // explode the host string by each existing dot '.' into parts
      $hostParts         = \explode( '.', $host );

      if ( \is_numeric( $hostParts[ \count( $hostParts ) - 1 ] ) )
      {
         // TLD can not be numeric => is a IP address or generally a wrong format
         return false;
      }

      // Work with a copy
      $_host = $host;

      if ( false !== ( $_tld = TopLevelDomain::ParseExtract( $_host, $allowOnlyKnownTlds, false ) ) )
      {
         // Have found an usable TLD, use it
         $parsedSldOut = new SecondLevelDomain( '', $_tld );
         // Remember the RESERVED state
         $parsedSldOut->states[ 'RESERVED' ]  = $_tld->isReserved();
         // $_host now do not contain an TLD, so we must explode it for new usage
         $hostParts = \explode( '.', $_host );
         // Get and remember the state if the host is a known URL shortener
         $parsedSldOut->states[ 'SHORTENER' ] = \in_array(
            \strtolower( $hostParts[ \count( $hostParts ) - 1 ] . '.' . $_tld ),
            static::URL_SHORTENERS,
            true
         );
      }
      else
      {
         // No TLD was found
         if ( $allowOnlyKnownTlds && \count( \explode( '.', $host ) ) > 2 )
         {
            // Only known TLDs are accepted but the host have none
            return false;
         }
         if ( $allowOnlyKnownTlds && ! TopLevelDomain::EndsWithValidTldString( $host ) )
         {
            // Only known TLDs are accepted but the host have none
            return false;
         }
         // Init a empty SLD
         $parsedSldOut = new SecondLevelDomain( '' );
      }

      if ( ! \preg_match( '~^[a-z0-9_][a-z.0-9_-]+$~i', $_host ) )
      {
         // invalid host name format
         return false;
      }

      $tmp = \explode( '.', $_host );
      if ( \count( $tmp ) >= 2 )
      {
         $_sld   = $tmp[ \count( $tmp ) - 1 ];
         $_thild = \implode( '.', ArrayHelper::Extract( $tmp, 0, \count( $tmp ) - 1 ) );
      }
      else
      {
         $_sld   = $_host;
         $_thild = '';
      }

      $parsedSldOut->_hostName = $_sld;

      $sld = $_sld . ( $parsedSldOut->hasTLD() ? ( '.' . $parsedSldOut->_tld->toString() ) : '' );

      if ( \preg_match( static::LOCAL_HOSTS, $sld ) )
      {
         $parsedSldOut->states[ 'LOCAL' ]     = true;
         $parsedSldOut->states[ 'RESERVED' ]  = true;
      }
      else if ( \preg_match( static::DYN_DNS_SERVICES, $sld ) )
      {
         $parsedSldOut->states[ 'DYNAMIC' ]  = true;
      }

      if ( ! $parsedSldOut->states[ 'RESERVED' ] && \preg_match( static::RESERVED_HOSTS, $sld ) )
      {
         $parsedSldOut->states[ 'RESERVED' ]  = true;
      }

      $host = $_thild;

      return true;

   }

   /**
    * Extracts a Second level domain definition from a full host definition like 'www.example.com' => 'example.com'.
    * The rest (Third level label (often called 'Sub domain name')) is returned by $host, if the method returns a
    * valid {@see \Niirrty\Web\SecondLevelDomain} instance.
    *
    * @param  string  $host               The full host definition and it returns the resulting third level label
    *                                     (known as sub domain name) if the method returns a new instance
    * @param  boolean $allowOnlyKnownTlds Are only known main TLDs allowed to be a parsed as a TLD?
    * @param  bool    $convertUniCode     Convert unicode SLDs to Puny code? (Default = TRUE)
    * @return \Niirrty\Web\SecondLevelDomain|bool
    */
   public static function ParseExtract( string &$host, bool $allowOnlyKnownTlds = false, bool $convertUniCode = false )
   {

      if ( $convertUniCode ) { $host = idnToASCII( $host ); }

      if ( empty( $host ) )
      {
         // NULL values or none string values will always return FALSE
         return false;
      }

      // explode the host string by each existing dot '.' into parts
      $hostParts         = \explode( '.', $host );

      if ( \is_numeric( $hostParts[ \count( $hostParts ) - 1 ] ) )
      {
         // TLD can not be numeric => is a IP address or generally a wrong format
         return false;
      }

      // Work with a copy
      $_host = $host;

      if ( false !== ( $_tld = TopLevelDomain::ParseExtract( $_host, $allowOnlyKnownTlds, false ) ) )
      {
         // Have found an usable TLD, use it
         $parsedSldOut = new SecondLevelDomain( '', $_tld );
         // Remember the RESERVED state
         $parsedSldOut->states[ 'RESERVED' ]  = $_tld->isReserved();
         // $_host now do not contain an TLD, so we must explode it for new usage
         $hostParts = \explode( '.', $_host );
         // Get and remember the state if the host is a known URL shortener
         $parsedSldOut->states[ 'SHORTENER' ] = \in_array(
            \strtolower( $hostParts[ \count( $hostParts ) - 1 ] . '.' . $_tld ),
            static::URL_SHORTENERS,
            true
         );
      }
      else
      {
         // No TLD was found
         if ( $allowOnlyKnownTlds && \count( \explode( '.', $host ) ) > 2 )
         {
            // Only known TLDs are accepted but the host have none
            return false;
         }
         if ( $allowOnlyKnownTlds && ! TopLevelDomain::EndsWithValidTldString( $host ) )
         {
            // Only known TLDs are accepted but the host have none
            return false;
         }
         // Init a empty SLD
         $parsedSldOut = new SecondLevelDomain( '' );
      }

      if ( ! \preg_match( '~^[a-z0-9_][a-z.0-9_-]+$~i', $_host ) )
      {
         // invalid host name format
         return false;
      }

      $tmp = \explode( '.', $_host );
      if ( \count( $tmp ) >= 2 )
      {
         $_sld   = $tmp[ \count( $tmp ) - 1 ];
         $_thild = \implode( '.', ArrayHelper::Extract( $tmp, 0, \count( $tmp ) - 1 ) );
      }
      else
      {
         $_sld   = $_host;
         $_thild = '';
      }

      $parsedSldOut->_hostName = $_sld;

      $sld = $_sld . ( $parsedSldOut->hasTLD() ? ( '.' . $parsedSldOut->_tld->toString() ) : '' );

      if ( \preg_match( static::LOCAL_HOSTS, $sld ) )
      {
         $parsedSldOut->states[ 'LOCAL' ]     = true;
         $parsedSldOut->states[ 'RESERVED' ]  = true;
      }
      else if ( \preg_match( static::DYN_DNS_SERVICES, $sld ) )
      {
         $parsedSldOut->states[ 'DYNAMIC' ]  = true;
      }

      if ( ! $parsedSldOut->states[ 'RESERVED' ] && \preg_match( static::RESERVED_HOSTS, $sld ) )
      {
         $parsedSldOut->states[ 'RESERVED' ]  = true;
      }

      $host = $_thild;

      return $parsedSldOut;

   }

   // </editor-fold>
   

}

