<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2016-2024, Ni Irrty
 * @package        Niirrty\Web
 * @since          2017-11-02
 */


declare( strict_types=1 );


namespace Niirrty\Web;


use Niirrty\IToString;

/**
 * This class defines a TopLevelDomain part of a host name.
 *
 * <code>
 * third-level-domain-label  .  second-level-domain-label  .  top-level-domain-label  .  root-label
 * www                       .  example                    .  com
 * </code>
 *
 * The TLD from example above is <b>com</b> or <b>com.</b> (last is fully qualified)
 *
 * For some validation reasons the class can get &amp; store some testing states, informing about TopLevelDomain
 * details.
 *
 * All this states can be accessed via associated is*() methods.
 */
class TopLevelDomain implements IToString
{


    #region // – – –   P R I V A T E   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – –

    /**
     * The TopLevelDomain value string.
     *
     * @var string
     */
    private string $value;

    /**
     * All state info about current TopLevelDomain.
     *
     * @var array
     */
    private array $states;

    #endregion


    #region // – – –   P R I V A T E   C O N S T A N T S   – – – – – – – – – – – – – – – – – – – – –

    /**
     * Known TLD formats regexp part.
     *
     * @type string
     */
    protected const string KNOWN_FORMAT = 'xn--[a-z\d-]{3,24}|[a-z]{2,12}|wow64';

    /**
     * Known generic TLDs regexp part.
     *
     * @type string
     */
    protected const string KNOWN_GENERIC = 'com|edu|gov|int|mil|net|org';

    /**
     * Known reserved TLDs regexp part.
     *
     * @type string
     */
    protected const string KNOWN_RESERVED = 'arpa|example|test|tld';

    /**
     * Known country specific TLDs regexp part.
     *
     * @type string
     */
    protected const string KNOWN_COUNTRY = 'a[cdefgilmnoqrstuwxz]|b[abmnorstvwyzd-j]|c[acdrf-ik-ou-z]|d[ejkmoz]|e[cegrstu]|f[ijkmor]|g[abdefghilmnpqrstuwy]|h[kmnrtu]|i[delmnoqrst]|j[emop]|k[eghimnqrwz]|l[abcikrstuvy]|m[acdeghk-z]|n[acefgilopruz]|om|p[aefghklmnrstwy]|qa|r[eosuw]|s[xyza-eg-or-v]|t[cdfghrstvwzj-p]|u[agksyz]|v[aceginu]|w[fs]|y[etu]|z[amrw]|co\.uk|com.au';

    /**
     * Known country specific puny-coded unicode TLDs regexp part.
     *
     * @type string
     */
    protected const string KNOWN_LC_COUNTRY = 'xn--(3e0b707e|45brj9c|54b7fta0cc|80ao21a|90a(is|3ac)|clchc0ea0b2g2a9gcd|d1alf|fiq(s8|z9)s|fpcrj9c3d|fzc2c9e2c|gecrj9c|h2brj9c|j1amh|j6w193g|kpr(w13d|y57d)|l1acc|lgbbat1ad8j|mgb(2ddes|9awbf|a3a4f16a|aam7a8h|ai9azgqp6j|ayh7gpa|bh1a71e|c0a9azcg|erp4a5d4ar|pl2fh|tx2b|x4cd0ab|xkc2al3hye2a)|node|o3cw4h|ogbpf8fl|p1ai|pgbs0dh|s9brj9c|wgb(h1c|l6a)|xkc2dl3a5ee0h|yfro4i67o|ygbi2ammx|y9a3aq)';

    /**
     * Known generic puny-coded unicode TLDs regexp part.
     *
     * @type string
     */
    protected const string KNOWN_LC_GENERIC = 'xn--(3ds443g|55qx5d|6frz82g|6qq986b3xl|80asehdb|80aswg|c1avg|czr694b|czru2d|d1acj3b|fiq228c5hs|i1b6b1a6a2e|io0a7i|ngbc5azd|nqv7f|mgbab2bd|q9jyb4c|rhqv96g|ses554g)';

    /**
     * Known geographic TLDs regexp part.
     *
     * @type string
     */
    protected const string KNOWN_GEOGRAPHIC = 'asia|bayern|berlin|brussels|budapest|bzh|cat|cologne|cymru|hamburg|kiwi|koeln|london|moscow|nagoya|nyc|okinawa|paris|ruhr|saarland|tirol|tokyo|vegas|vlaanderen|wales|wien|yokohama|москва|xn--80adxhks';

    /**
     * Known double TLDs regexp part.
     *
     * @type string
     */
    protected const string DOUBLE_TLDS = '((co|or)\.at|(com|nom|org)\.es|(ac|co|gov|ltd|me|net|nic|nhs|org|plc|sch)\.uk|(biz|com|info|net|org)\.pl|(com|net|org)\.vc|(com|org)\.au|(com|tv|net)\.br)';

    #endregion


    #region // – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –

    /**
     * Init a new instance.
     *
     * @param string|null $tldValue The TopLevelDomain string value, to associate the instance with.
     */
    private function __construct( ?string $tldValue )
    {

        $this->value = $tldValue ?? '';

    }

    #endregion


    #region // – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –

    /**
     * Returns the the TopLevelDomain string value of this instance. If its defined as a fully qualified TLD
     * (it must ends with a dot) it is returned with the trailing dot, otherwise without it.
     *
     * @return string
     */
    public function __toString()
    {

        // If value is null, or not a string, return a empty string ''. otherwise the value is returned.
        return $this->value . ( $this->isFullyQualified() ? '.' : '' );

    }

    /**
     * Returns always the fully qualified TLD. A fully qualified TLD always ends with a dot (like 'com.')
     *
     * @return string
     */
    public function toFullyQualifiedString(): string
    {

        return $this->value . '.';

    }

    /**
     * Returns always the NOT fully qualified TLD, also if a fully qualified TLD is used.
     *
     * @return string
     */
    public function toString(): string
    {

        return $this->value;

    }

    /**
     * Is the current TopLevelDomain value a known GENERIC TopLevelDomain? Generic TopLevelDomains are:
     * 'com', 'edu', 'gov', 'int', 'mil', 'net', 'org'. Includes also some localized generic TLDs.
     *
     * @return boolean
     */
    public function isGeneric(): bool
    {

        return (bool) $this->states[ 'GENERIC' ];

    }

    /**
     * Is the current TopLevelDomain value a known RESERVED TopLevelDomain? Reserved TLDs are: 'arpa', 'example',
     * 'test' and 'tld'
     *
     * @return boolean
     */
    public function isReserved(): bool
    {

        return (bool) $this->states[ 'RESERVED' ];

    }

    /**
     * Is the current TopLevelDomain value a known COUNTRY TopLevelDomain? Country TLDs are: 'cz', 'de', 'en', etc.
     * Includes also some localized country TLDs.
     *
     * @return boolean
     */
    public function isCountry(): bool
    {

        return (bool) $this->states[ 'COUNTRY' ];

    }

    /**
     * Is the current TopLevelDomain value a known GEOGRAPHIC TopLevelDomain? Geographic TopLevelDomains are:
     * 'asia', 'berlin', 'london', etc.
     *
     * @return boolean
     */
    public function isGeographic(): bool
    {

        return (bool) $this->states[ 'GEOGRAPHIC' ];

    }

    /**
     * Is the current TopLevelDomain value a known LOCALIZED UNICODE TopLevelDomain? Localized TLDs are already
     * starting with 'xn--'.
     *
     * It can be combined with Generic or a Country TLD.
     *
     * @return boolean
     */
    public function isLocalized(): bool
    {

        return (bool) $this->states[ 'LOCALIZED' ];

    }

    /**
     * Is the current TopLevelDomain value a TLD defined in full qualified manner? It means, is the root-label
     * (always a empty string separated by a dot) defined? e.g.: 'com.'.
     *
     * @return boolean
     */
    public function isFullyQualified(): bool
    {

        return (bool) $this->states[ 'FULLYQUALIFIED' ];

    }

    /**
     * Is the current TopLevelDomain value a public known, registered TLD?
     *
     * @return boolean
     */
    public function isKnown(): bool
    {

        return (bool) $this->states[ 'KNOWN' ];

    }

    /**
     * Is the TLD a double TLD like co.uk?
     *
     * @return boolean
     */
    public function isDouble(): bool
    {

        return (bool) $this->states[ 'DOUBLE' ];

    }

    #endregion


    #region // – – –   P R I V A T E   S T A T I C   M E T H O D S   – – – – – – – – – – – – – – – –

    private static function initStates(): array
    {

        // Init the states with the default values
        return [
            'DOUBLE'         => false,
            'GENERIC'        => false,
            'RESERVED'       => false,
            'COUNTRY'        => false,
            'GEOGRAPHIC'     => false,
            'LOCALIZED'      => false,
            'FULLYQUALIFIED' => false,
            'KNOWN'          => false,
        ];

    }

    private static function parseForStates( TopLevelDomain $tld ): TopLevelDomain
    {

        // FULLYQUALIFIED
        if ( \str_ends_with( $tld->value, '.' ) )
        {
            // Its a full qualified TLD, ending with a dot, remember it…
            $tld->states[ 'FULLYQUALIFIED' ] = true;
            // remove the trailing dot
            $tld->value = \substr( $tld->value, 0, -1 );
        }

        // Next we check if the TopLevelDomain is a known generic TopLevelDomain
        if ( \preg_match( '~^(' . static::DOUBLE_TLDS . ')$~i', $tld->value ) )
        {
            $tld->states[ 'DOUBLE' ] = true;
            $tld->states[ 'KNOWN' ] = true;
        }

        // Next we check if the TopLevelDomain is a known generic TopLevelDomain
        if ( !$tld->states[ 'DOUBLE' ] && \preg_match( '~^(' . static::KNOWN_GENERIC . ')$~i', $tld->value ) )
        {
            $tld->states[ 'GENERIC' ] = true;
            $tld->states[ 'KNOWN' ] = true;
        }

        // Next we check if the TopLevelDomain is a known RESERVED TopLevelDomain
        if ( !$tld->states[ 'DOUBLE' ] && !$tld->states[ 'GENERIC' ]
             && \preg_match( '~^(' . static::KNOWN_RESERVED . ')$~i', $tld->value ) )
        {
            $tld->states[ 'RESERVED' ] = true;
            $tld->states[ 'KNOWN' ] = true;
        }

        // Now we check if the TopLevelDomain is a known GEOGRAPHIC TopLevelDomain
        if ( !$tld->states[ 'DOUBLE' ] && !$tld->states[ 'GENERIC' ] && !$tld->states[ 'RESERVED' ]
             && \preg_match( '~^(' . static::KNOWN_GEOGRAPHIC . ')$~i', $tld->value ) )
        {
            $tld->states[ 'GEOGRAPHIC' ] = true;
            $tld->states[ 'KNOWN' ] = true;
        }

        // Now we check if the TopLevelDomain is a known LOCALIZED GENERIC TopLevelDomain
        if ( !$tld->states[ 'DOUBLE' ] && !$tld->states[ 'GENERIC' ] && !$tld->states[ 'RESERVED' ]
             && !$tld->states[ 'GEOGRAPHIC' ] && \preg_match( '~^(' . static::KNOWN_LC_GENERIC . ')$~i', $tld->value ) )
        {
            $tld->states[ 'GENERIC' ] = true;
            $tld->states[ 'LOCALIZED' ] = true;
            $tld->states[ 'KNOWN' ] = true;
        }

        // Now we check if the TopLevelDomain is a known LOCALIZED COUNTRY TopLevelDomain
        if ( !$tld->states[ 'DOUBLE' ] && !$tld->states[ 'GENERIC' ] && !$tld->states[ 'RESERVED' ]
             && !$tld->states[ 'GEOGRAPHIC' ] && !$tld->states[ 'LOCALIZED' ]
             && \preg_match( '~^(' . static::KNOWN_LC_COUNTRY . ')$~i', $tld->value ) )
        {
            $tld->states[ 'COUNTRY' ] = true;
            $tld->states[ 'LOCALIZED' ] = true;
            $tld->states[ 'KNOWN' ] = true;
        }

        // Next we check if the TopLevelDomain is a known COUNTRY TopLevelDomain
        if ( \preg_match( '~^(' . static::KNOWN_COUNTRY . ')$~i', $tld->value ) )
        {
            $tld->states[ 'COUNTRY' ] = true;
            $tld->states[ 'KNOWN' ] = true;
        }

        if ( \str_contains( $tld->value, 'xn--' ) )
        {
            $tld->states[ 'LOCALIZED' ] = true;
            $tld->states[ 'KNOWN' ] = true;
        }

        return $tld;

    }

    #endregion


    #region // – – –   P U B L I C   S T A T I C   M E T H O D S   – – – – – – – – – – – – – – – – –

    /**
     * Parses the defined TLD string to a {@see TopLevelDomain} instance. On error it returns FALSE.
     *
     * @param string|null         $tld            The TLD to parse.
     * @param TopLevelDomain|null $parsedTldOut   Returns the TopLevelDomain if the method returns true
     * @param bool                $allowOnlyKnown Are only known main TLDs allowed to be a parsed as a TLD?
     * @param bool                $convertUniCode Convert unicode TLDs to Puny code? (Default = TRUE)
     * @return bool   Return TRUE on success ($parsedTldOut returns the TopLevelDomain) or FALSE otherwise.
     */
    public static function TryParse(
        ?string $tld, ?TopLevelDomain &$parsedTldOut = null, bool $allowOnlyKnown = false,
        bool $convertUniCode = true ): bool
    {

        if ( $convertUniCode )
        {
            $tld = idnToASCII( $tld );
        }

        if ( empty( $tld ) || ! \is_string( $tld ) )
        {
            // NULL values or none string or empty values will always return FALSE
            return false;
        }

        // This is the default regexp, used if its not required to be a known TLD, only a valid format is required
        $regex = '~^(' . static::DOUBLE_TLDS . '|' . static::KNOWN_FORMAT . ')\.?$~i';

        if ( $allowOnlyKnown )
        {
            // init the extended regexp, if only known TLDs are accepted
            $regex = '~^(' . static::DOUBLE_TLDS . '|' . static::KNOWN_GENERIC . '|' . static::KNOWN_COUNTRY
                   . '|' . static::KNOWN_GEOGRAPHIC . '|' . static::KNOWN_LC_COUNTRY . '|' . static::KNOWN_LC_GENERIC
                   . '|' . static::KNOWN_RESERVED . ')\.?$~i';
        }

        if ( ! \preg_match( $regex, $tld ) )
        {
            // $tld have no valid TLD defined
            return false;
        }

        // Init the TLD instance with extracted TLD value
        $parsedTldOut = new TopLevelDomain( $tld );
        // Init the states
        $parsedTldOut->states = static::initStates();

        // Find the corresponding states and return the TLD instance
        $parsedTldOut = static::parseForStates( $parsedTldOut );

        return true;

    }

    /**
     * Parses the defined TLD string to a {@see TopLevelDomain} instance. On error it returns FALSE.
     *
     * @param string|null $tld            The TLD to parse.
     * @param bool        $allowOnlyKnown Are only known main TLDs allowed to be a parsed as a TLD?
     * @param bool        $convertUniCode Convert unicode TLDs to Puny code? (Default = TRUE)
     *
     * @return TopLevelDomain|false   Returns the TopLevelDomain or FALSE on error.
     */
    public static function Parse(
        ?string $tld, bool $allowOnlyKnown = false, bool $convertUniCode = true ) : TopLevelDomain|false
    {

        if ( $convertUniCode )
        {
            $tld = idnToASCII( $tld );
        }

        if ( empty( $tld ) || ! \is_string( $tld ) )
        {
            // NULL values or none string or empty values will always return FALSE
            return false;
        }

        // This is the default regexp, used if its not required to be a known TLD, only a valid format is required
        $regex = '~^(' . static::DOUBLE_TLDS . '|' . static::KNOWN_FORMAT . ')\.?$~i';

        if ( $allowOnlyKnown )
        {
            // init the extended regexp, if only known TLDs are accepted
            $regex = '~^(' . static::DOUBLE_TLDS . '|' . static::KNOWN_GENERIC . '|' . static::KNOWN_COUNTRY . '|'
                   . static::KNOWN_GEOGRAPHIC . '|' . static::KNOWN_LC_COUNTRY . '|' . static::KNOWN_LC_GENERIC . '|'
                   . static::KNOWN_RESERVED . ')\.?$~i';
        }

        if ( ! \preg_match( $regex, $tld ) )
        {
            // $tld have no valid TLD defined
            return false;
        }

        // Init the TLD instance with extracted TLD value
        $parsedTldOut = new TopLevelDomain( $tld );
        // Init the states
        $parsedTldOut->states = static::initStates();

        // Find the corresponding states and return the TLD instance
        return static::parseForStates( $parsedTldOut );

    }

    /**
     * Extracts the TopLevelDomain from defined host name string.
     *
     * @param string              $hostString              The Host name string value reference to parse. After parsing,
     *                                                     a maybe defined TopLevelDomain is removed from this variable.
     * @param TopLevelDomain|null $parsedTldOut            Returns the TopLevelDomain if the method returns true
     * @param bool                $allowOnlyKnown          Are only known main TLDs allowed to be a parsed as a TLD?
     * @param bool                $convertUniCode          Convert unicode Hosts to Puny code? (Default = FALSE)
     *
     * @return bool   Return TRUE on success ($parsedTldOut returns the TopLevelDomain) or FALSE otherwise.
     */
    public static function TryParseExtract(
        string &$hostString, ?TopLevelDomain &$parsedTldOut = null, bool $allowOnlyKnown = false,
        bool $convertUniCode = false ): bool
    {

        if ( $convertUniCode )
        {
            $hostString = idnToASCII( $hostString );
        }

        if ( empty( $hostString ) )
        {
            // NULL values or none string values will always return FALSE
            return false;
        }

        // This is the default regexp, used if its not required to be a known TLD, only a valid format is required
        $regex = '~^(.+?)\.(' . static::DOUBLE_TLDS . '\.?)$~i';

        if ( ! \preg_match( $regex, $hostString, $matches ) )
        {
            $regex = '~^(.+?)\.((' . static::KNOWN_FORMAT . ')\.?)$~i';
            if ( $allowOnlyKnown )
            {
                // init the extended regexp, if only known TLDs are accepted
                $regex = '~^(.+)\.(((' . static::DOUBLE_TLDS . ')|' . static::KNOWN_GENERIC . '|'
                       . static::KNOWN_COUNTRY . '|' . static::KNOWN_GEOGRAPHIC . '|' . static::KNOWN_LC_COUNTRY . '|'
                       . static::KNOWN_LC_GENERIC . '|' . static::KNOWN_RESERVED . ')\.?)$~i';
            }
            if ( ! \preg_match( $regex, $hostString, $matches ) )
            {
                // $hostString have no valid TLD defined
                return false;
            }
        }

        // Reassign the host string without the TLD
        $hostString = $matches[ 1 ];
        // Init the TLD instance with extracted TLD value
        $parsedTldOut = new TopLevelDomain( $matches[ 2 ] );
        // Init the states
        $parsedTldOut->states = static::initStates();

        // Find the corresponding states and return the TLD instance
        $parsedTldOut = static::parseForStates( $parsedTldOut );

        return true;

    }

    /**
     * Extracts the TopLevelDomain from defined host name string.
     *
     * @param string $hostString                           The Host name string value reference to parse. After parsing,
     *                                                     a maybe defined TopLevelDomain is removed from this variable.
     * @param bool   $allowOnlyKnown                       Are only known main TLDs allowed to be a parsed as a TLD?
     * @param bool   $convertUniCode                       Convert unicode Hosts to Puny code? (Default = FALSE)
     *
     * @return TopLevelDomain|false   Returns the TopLevelDomain or FALSE on error.
     */
    public static function ParseExtract(
        string &$hostString, bool $allowOnlyKnown = false, bool $convertUniCode = false ): TopLevelDomain|false
    {

        if ( $convertUniCode )
        {
            $hostString = idnToASCII( $hostString );
        }

        if ( empty( $hostString ) )
        {
            // NULL values or none string values will always return FALSE
            return false;
        }

        // This is the default regexp, used if its not required to be a known TLD, only a valid format is required
        $regex = '~^(.+?)\.(' . static::DOUBLE_TLDS . '\.?)$~i';

        if ( ! \preg_match( $regex, $hostString, $matches ) )
        {
            $regex = '~^(.+?)\.((' . static::KNOWN_FORMAT . ')\.?)$~i';
            if ( $allowOnlyKnown )
            {
                // init the extended regexp, if only known TLDs are accepted
                $regex = '~^(.+)\.((' . static::DOUBLE_TLDS . '|' . static::KNOWN_GENERIC . '|' . static::KNOWN_COUNTRY
                       . '|' . static::KNOWN_GEOGRAPHIC . '|' . static::KNOWN_LC_COUNTRY . '|'
                       . static::KNOWN_LC_GENERIC . '|' . static::KNOWN_RESERVED . ')\.?)$~i';
            }
            if ( ! \preg_match( $regex, $hostString, $matches ) )
            {
                // $hostString have no valid TLD defined
                return false;
            }
        }

        // Reassign the host string without the TLD
        $hostString = $matches[ 1 ];
        // Init the TLD instance with extracted TLD value
        $parsedTldOut = new TopLevelDomain( $matches[ 2 ] );
        // Init the states
        $parsedTldOut->states = static::initStates();

        // Find the corresponding states and return the TLD instance
        return static::parseForStates( $parsedTldOut );

    }

    /**
     * Returns if the defined string ends with a substring, defined by characters, usable as a TLD.
     *
     * @param string $stringToCheck
     * @param bool   $convertUniCode Convert unicode strings to Puny code? (Default = FALSE)
     *
     * @return boolean
     */
    public static function EndsWithValidTldString( string &$stringToCheck, bool $convertUniCode = false ): bool
    {

        if ( $convertUniCode )
        {
            $stringToCheck = idnToASCII( $stringToCheck );
        }

        return (bool) \preg_match(
            '~\.(' . static::DOUBLE_TLDS . '|' . static::KNOWN_FORMAT . ')\.?$~i',
            $stringToCheck
        );

    }

    #endregion


}

