<?php /** @noinspection PhpUnused */
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2016-2020, Ni Irrty
 * @package        Niirrty\Web
 * @since          2017-11-02
 * @version        0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\Web;


/**
 * Defines a domain. A domain is represented by a sub domain name (3rd+ level domain name label) a second
 * level domain (2nd level domain name label) the TLD (top level domain name label) and a root label.
 *
 * The sub domain name and the root label is always optionally. Second level domain and/or TLD must be defined.
 * (At least only one of it is required!)
 *
 * <code>
 * third-level-domain-label  .  second-level-domain-label  .  top-level-domain-label  .  root-label
 * www                       .  example                    .  com
 * </code>
 *
 * The domain from example above is <b>www.example.com</b> or <b>www.example.com.</b> (last is fully qualified)
 *
 * For some validation reasons the class can get &amp; store some testing states, informing about
 * Domain details.
 */
final class Domain
{


    // <editor-fold desc="// – – –   P R I V A T E   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – –">


    /**
     * The name of the optional sub domain (third+ level domain label). If no sub domain exists the value is NULL.
     *
     * @var string|NULL
     */
    private $_subDomainName;

    /**
     * The contained second level domain part.
     *
     * @var SecondLevelDomain
     */
    private $_sld;

    /**
     * A array of states (Detail information about the host)
     *
     * @var array
     */
    private $states;

    // </editor-fold>


    // <editor-fold desc="// – – –   P R I V A T E   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – –">

    private function __construct( ?string $subdomainName, ?SecondLevelDomain $sld = null )
    {

        $this->_subDomainName = $subdomainName;
        $this->_sld = $sld;
        $value = (string) $this;
        $this->states = [
            'IPV4ADDRESS' => isIPv4Address( $value ),
            'IPV6ADDRESS' => isIPv6Address( $value ),
            'LOCAL'       => (bool) \preg_match(
                "~^(127(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){3}|172\.(1[6-9]|2\d|3[01])(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){2}|192\.168(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){2})$~",
                $value
            ),
            'RESERVED'    => (bool) \preg_match(
                '~^(127(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){3}|(100\.(6[4-9]|[7-9]\d|1([01]\d|2[0-7]))|169\.254|172\.(1[6-9]|2\d|3[01])|192\.168|198\.1[89])(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){2}|192\.0\.[02]\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))|198\.51\.100\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))|192\.88\.99\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5]))))$~',
                $value
            ),
        ];

    }

    // </editor-fold>


    // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">

    /**
     * Returns the the Domain string value of this instance. If its defined as a fully qualified Domain
     * (it must ends with a dot) its returned with the trailing dot, otherwise without it.
     *
     * @return string
     */
    public function __toString()
    {

        if ( empty( $this->_subDomainName ) )
        {

            if ( null === $this->_sld )
            {
                return '';
            }

            return (string) $this->_sld;

        }

        if ( null === $this->_sld )
        {
            return (string) ( $this->_subDomainName ?? '' );
        }

        return $this->_subDomainName . '.' . $this->_sld;

    }

    /**
     * Returns the fully qualified Domain. A fully qualified Domain always ends with a dot (like 'www.example.com.')
     *
     * @return string
     */
    public function toFullyQualifiedString(): string
    {

        if ( empty( $this->_subDomainName ) )
        {

            if ( null === $this->_sld )
            {
                return '';
            }

            return $this->_sld->toFullyQualifiedString();

        }

        if ( null === $this->_sld )
        {
            return $this->_subDomainName;
        }

        return $this->_subDomainName . '.' . $this->_sld->toFullyQualifiedString();

    }

    /**
     * Returns always the NOT fully qualified Domain, also if a fully qualified Domain is used.
     *
     * @return string
     */
    public function toString(): string
    {

        if ( empty( $this->_subDomainName ) )
        {

            if ( null === $this->_sld )
            {
                return '';
            }

            return $this->_sld->toString();

        }

        if ( null === $this->_sld )
        {
            return $this->_subDomainName;
        }

        return $this->_subDomainName . '.' . $this->_sld->toString();

    }

    /**
     * Gets the sub domain name part if defined. IP Addresses always return a empty string!
     *
     * @return string
     */
    public function getSubDomainName(): string
    {

        return $this->isIPAddress() ? '' : $this->_subDomainName;

    }

    /**
     * Gets if the current host/domain points to an IP-Address
     *
     * @return bool
     */
    public function isIPAddress(): bool
    {

        return $this->states[ 'IPV4ADDRESS' ] || $this->states[ 'IPV6ADDRESS' ];

    }

    /**
     * Gets if the current host/domain points to an IPv4-Address
     *
     * @return bool
     */
    public function isIPv4Address(): bool
    {

        return $this->states[ 'IPV4ADDRESS' ];

    }

    /**
     * Gets if the current host/domain points to an IPv6-Address
     *
     * @return bool
     */
    public function isIPv6Address(): bool
    {

        return $this->states[ 'IPV6ADDRESS' ];

    }

    /**
     * Gets the IP address string if the domain is an IP address.
     *
     * @return string
     */
    public function getIPAddress(): string
    {

        return !$this->isIPAddress() ? '' : (string) $this;

    }

    /**
     * Gets the second level domain part if defined.
     *
     * @return SecondLevelDomain|null
     */
    public function getSecondLevelDomain(): ?SecondLevelDomain
    {

        return $this->_sld;

    }

    /**
     * Is the host defined in full qualified manner? It means, is the root-label (always a empty string separated by
     * a dot) defined? e.g.: 'example.com.'
     *
     * @return bool
     */
    public function isFullyQualified(): bool
    {

        if ( null === $this->_sld )
        {
            return false;
        }

        return $this->_sld->isFullyQualified();

    }

    /**
     * Returns if a usable TLD is defined.
     *
     * @return bool
     */
    public function hasTLD(): bool
    {

        if ( null === $this->_sld )
        {
            return false;
        }

        return $this->_sld->hasTLD();

    }

    /**
     * Returns if the TLD is a generally known, registered TLD.
     *
     * @return bool
     */
    public function hasDoubleTLD(): bool
    {

        if ( null === $this->_sld )
        {
            return false;
        }

        return $this->_sld->hasDoubleTLD();

    }

    /**
     * Returns if the TLD is a known double TLD like co.uk.
     *
     * @return bool
     */
    public function hasKnownTLD(): bool
    {

        if ( null === $this->_sld )
        {
            return false;
        }

        return $this->_sld->hasKnownTLD();

    }

    /**
     * Returns if the current Host uses a sub domain.
     *
     * @return bool
     */
    public function hasSubDomain(): bool
    {

        return !$this->isIPAddress() && !empty( $this->_subDomainName );

    }

    /**
     * Is the current TLD value (if defined) a known COUNTRY TLD? Country TopLevelDomains are: 'cz', 'de', etc.
     * and also localized country TopLevelDomains (xn--…).
     *
     * @return bool
     */
    public function isCountry(): bool
    {

        if ( null === $this->_sld )
        {
            return false;
        }

        return $this->_sld->isCountry();

    }

    /**
     * Is the current TLD value (if defined) a known GENERIC TLD? Generic TopLevelDomains are: 'com', 'edu', 'gov',
     * 'int', 'mil', 'net', 'org' and also the associated localized TopLevelDomains (xn--…).
     *
     * @return bool
     */
    public function isGeneric(): bool
    {

        if ( null === $this->_sld )
        {
            return false;
        }

        return $this->_sld->isGeneric();

    }

    /**
     * Is the current TLD value (if defined) a GEOGRAPHIC TLD? Geogr. TLDs are: 'asia', 'berlin', 'london', etc.
     *
     * @return bool
     */
    public function isGeographic(): bool
    {

        if ( null === $this->_sld )
        {
            return false;
        }

        return $this->_sld->isGeographic();

    }

    /**
     * Is the current TLD (if defined) a known LOCALIZED UNICODE TLD? Localized TLDs are starting with 'xn--'.
     *
     * @return bool
     */
    public function isLocalized(): bool
    {

        if ( null === $this->_sld )
        {
            return false;
        }

        return $this->_sld->isLocalized();

    }

    /**
     * Is the SLD (Hostname or TLD or both or ID-Address) representing a reserved element? Reserved TLDs are:
     *
     * - arpa
     * - example
     * - test
     * - tld
     *
     * Reserved SLDs are:
     *
     * - example.(com|net|org)'
     * - local
     * - localhost
     * - localdomain
     *
     * Reserved IPv4 Address ranges are:
     *
     * - 0.0.0.0 - 0.255.255.255
     * - 10.0.0.0 - 10.255.255.255
     * - 127.0.0.0 - 127.255.255.255
     * - 100.64.0.0 - 100.127.255.255
     * - 169.254.0.0 - 169.254.255.255
     * - 172.16.0.0 - 172.31.255.255
     * - 192.0.0.0 - 192.0.0.255
     * - 198.51.100.0 - 198.51.100.255
     * - 192.88.99.0 - 192.88.99.255
     * - 192.168.0.0 - 192.168.255.255
     * - 198.18.0.0 - 198.19.255.255
     * - 224.0.0.0 - 255.255.255.255
     *
     * @return bool
     */
    public function isReserved(): bool
    {

        if ( $this->states[ 'RESERVED' ] || $this->states[ 'LOCAL' ] )
        {
            return true;
        }
        if ( null === $this->_sld )
        {
            return false;
        }

        return $this->_sld->isReserved();

    }

    /**
     * Is the SLD (Hostname or TLD or both) representing a local SLD(-Element)?
     *
     * @return bool
     */
    public function isLocal(): bool
    {

        if ( $this->states[ 'LOCAL' ] )
        {
            return true;
        }
        if ( null === $this->_sld )
        {
            return false;
        }

        return $this->_sld->isLocal();

    }

    /**
     * Is the SLD pointing to known public URL shortener service? They are used to shorten or hide some long or bad
     * URLs.
     *
     * @return bool
     */
    public function isUrlShortener(): bool
    {

        if ( null === $this->_sld )
        {
            return false;
        }

        return $this->_sld->isUrlShortener();

    }

    /**
     * Is the SLD pointing to a known public dynamic DNS service
     *
     * @return bool
     */
    public function isDynamic(): bool
    {

        if ( null === $this->_sld )
        {
            return false;
        }

        return $this->_sld->isDynamic();

    }

    // </editor-fold>


    // <editor-fold desc="// – – –   P U B L I C   S T A T I C   M E T H O D S   – – – – – – – – – – – – – – – – –">

    /**
     * Parses the defined Domain string to a {@see \Niirrty\Web\Domain} instance and returns if this was successful.
     *
     * @param string  $domainString       The domain string, including optional sub domain name, domain name and TLD.
     * @param Domain  $parsedDomainOut    Returns the Domain if the method returns true
     * @param boolean $allowOnlyKnownTlds Are only known main TLDs allowed to be a parsed as a TLD?
     * @param bool    $convertUniCode     Convert unicode Domain to Puny code? (Default = TRUE)
     *
     * @return bool
     */
    public static function TryParse(
        ?string $domainString, &$parsedDomainOut, bool $allowOnlyKnownTlds = false, bool $convertUniCode = true ): bool
    {

        if ( $convertUniCode )
        {
            $domainString = idnToASCII( $domainString );
        }

        if ( null === $domainString || '' === $domainString )
        {
            // NULL values or none string values will always return FALSE
            return false;
        }

        $_domainString = $domainString;

        if ( !SecondLevelDomain::TryParseExtract( $_domainString, $_sld, $allowOnlyKnownTlds, false ) )
        {
            if ( !\preg_match( "~^((\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){3}|([0-9a-fA-F]{1,4}(:[0-9a-fA-F]{1,4}){7}|::[0-9a-fA-F]{1,4}([0-9a-fA-F:.]+)?(/\d{1,3})?|::[0-9a-fA-F]{0,4})(/\d{1,3})?)$~",
                               $domainString ) )
            {
                return false;
            }
            else
            {
                $parsedDomainOut = new Domain( $domainString, null );

                return true;
            }
        }

        if ( !empty( $_domainString ) )
        {
            if ( !\preg_match( '~^[a-z0-9][a-z0-9_.-]*$~', $_domainString ) ||
                 \preg_match( '~(\.[^a-z0-9_]|[^a-z0-9_]\.)~', $_domainString ) ||
                 \preg_match( '~[^a-z0-9_]$~', $_domainString ) ||
                 \count( \explode( '.', $_domainString ) ) > 3 )
            {
                return false;
            }
        }
        else
        {
            $_domainString = null;
        }

        if ( $allowOnlyKnownTlds && !$_sld->HasKnownTLD )
        {
            return false;
        }

        $parsedDomainOut = new Domain( $_domainString, $_sld );

        return true;

    }

    /**
     * Parses the defined Domain string to a {@see \Niirrty\Web\Domain} instance and returns if this was successful.
     *
     * @param string  $domainString       The domain string, including optional sub domain name, domain name and TLD.
     * @param boolean $allowOnlyKnownTlds Are only known main TLDs allowed to be a parsed as a TLD?
     * @param bool    $convertUniCode     Convert unicode Domain to Puny code? (Default = TRUE)
     *
     * @return Domain|false
     */
    public static function Parse( ?string $domainString, bool $allowOnlyKnownTlds = false, bool $convertUniCode = true )
    {

        if ( $convertUniCode )
        {
            $domainString = idnToASCII( $domainString );
        }

        if ( null === $domainString || '' === $domainString )
        {
            // NULL values or none string values will always return FALSE
            return false;
        }

        $_domainString = $domainString;

        if ( false === ( $sld = SecondLevelDomain::ParseExtract( $_domainString, $allowOnlyKnownTlds, false ) ) )
        {
            if ( !\preg_match( "~^((\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){3}|([0-9a-fA-F]{1,4}(:[0-9a-fA-F]{1,4}){7}|::[0-9a-fA-F]{1,4}([0-9a-fA-F:.]+)?(/\d{1,3})?|::[0-9a-fA-F]{0,4})(/\d{1,3})?)$~",
                               $domainString ) )
            {
                return false;
            }
            else
            {
                return new Domain( $domainString, null );
            }
        }

        if ( !empty( $_domainString ) )
        {
            if ( !\preg_match( '~^[a-z0-9][a-z0-9_.-]*$~', $_domainString ) ||
                 \preg_match( '~(\.[^a-z0-9_]|[^a-z0-9_]\.)~', $_domainString ) ||
                 \preg_match( '~[^a-z0-9_]$~', $_domainString ) ||
                 \count( \explode( '.', $_domainString ) ) > 3 )
            {
                return false;
            }
        }
        else
        {
            $_domainString = null;
        }

        if ( $allowOnlyKnownTlds && !$sld->hasKnownTLD() )
        {
            return false;
        }

        return new Domain( $_domainString, $sld );

    }


    // </editor-fold>


}

