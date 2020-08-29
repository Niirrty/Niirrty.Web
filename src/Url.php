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


use function Niirrty\strContains;
use function Niirrty\strStartsWith;


/**
 * Splits a URL string to all usable elements/parts.
 *
 * <code>$Scheme://$AuthUser:$AuthPass@$Domain/$Path?$Query#$Anchor</code>
 */
class Url
{


    // <editor-fold desc="// – – –   P R I V A T E   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – –">


    /**
     * All possible open redirection URLs, contained inside the main URL.
     *
     * @var Url[]
     */
    private $openRedirectionURLs = [];

    /**
     * If open redirection bug usage was found here the result points are stored.
     *
     * @var int
     */
    private $lastOpenRedirectResultPoints = 0;

    /**
     * The URL scheme. Default is 'http'
     *
     * @type string
     */
    private $scheme;

    /**
     * The Domain/host of the URL
     *
     * @type Domain
     */
    private $domain;

    /**
     * The optional port if defined.
     *
     * @type int|null
     */
    private $port;

    /**
     * The optional auth user name part. (Usage is a security issue!)
     *
     * @type string|null
     */
    private $authUser;

    /**
     * The optional auth password part. (Usage is a security issue!)
     *
     * @type string|null
     */
    private $authPass;

    /**
     * The path part of the URL. If none is defined, '/' is used and returned.
     *
     * @type string
     */
    private $path;

    /**
     * The query parameters as associative array.
     *
     * @type array
     */
    private $query;

    /**
     * The optional URL anchor name without the leading '#'. If non is defined, NULL is used
     *
     * @type string|null
     */
    private $anchor;

    // </editor-fold>


    // <editor-fold desc="// – – –   P U B L I C   S T A T I C   F I E L D S   – – – – – – – – – – – – – – – – – –">

    /**
     * This scheme is used if none is defined. (default='http')
     *
     * @var string
     */
    public static $fallbackScheme = 'http';

    // </editor-fold>


    // <editor-fold desc="// – – –   C L A S S   C O N S T A N T S   – – – – – – – – – – – – – – – – – – – – – – –">

    /**
     * Finds all URLs inside a string to check. It returns the following match groups: 1=protocol, 2=host, 3=path+
     */
    protected const URL_FINDER = '~(https?|ftp)://([a-z0-9_.-]+)(/[a-z0-9_./+%?&#]+)?~i';

    // </editor-fold>


    // <editor-fold desc="// – – –   P R O T E C T E D   C O N S T R U C T O R   – – – – – – – – – – – – – – – – –">

    /**
     * Url constructor.
     *
     * @param string $scheme
     * @param Domain $domain
     */
    protected function __construct( string $scheme, Domain $domain )
    {

        $this->scheme = $scheme;
        $this->domain = $domain;
        $this->port = null;
        $this->authUser = null;
        $this->authPass = null;
        $this->path = '/';
        $this->query = [];
        $this->anchor = null;

    }

    // </editor-fold>


    // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">

    /**
     * Returns if URL contains some login data usable with AUTHTYPE BASIC. This is a security issue!
     *
     * @return boolean
     */
    public function hasLoginData(): bool
    {

        return null !== $this->authUser || null !== $this->authPass;

    }

    /**
     * Returns, if the current URL points to a IP address without using some host name, etc.
     *
     * @return boolean
     */
    public function useIpAddress(): bool
    {

        return $this->domain->isIPAddress();

    }

    /**
     * Returns if a port is used that points not to default port of current scheme/protocol.
     * If not explicit port is defined it always returns TRUE.
     *
     * @return boolean
     */
    public function useAssociatedPort(): bool
    {

        if ( null === $this->port )
        {
            return true;
        }

        switch ( $this->scheme )
        {

            case UrlScheme::HTTP:
                return ( $this->port === 80 );

            case UrlScheme::SSL:
                return ( $this->port === 443 );

            case UrlScheme::FTP:
                return ( $this->port === 21 );

            default :
                return false;

        }

    }

    /**
     * Returns, if current URL uses a known web scheme. Known web schemes (protocols) are 'http', 'https' and 'ftp'.
     *
     * @return boolean
     */
    public function isKnownWebScheme(): bool
    {

        return (bool) \preg_match( '~^(https?|ftp)$~', $this->scheme );

    }

    /**
     * Returns if the current URL points to a URL shortener service.
     *
     * @return boolean
     */
    public function isUrlShortenerAddress(): bool
    {

        return $this->domain->isUrlShortener();

    }

    /**
     * Gets the real url behind a shortener URL, if current URL points to a URL shortener service.
     *
     * @return Url|null Returns the real URL, or NULL.
     */
    public function extractUrlShortenerTarget(): ?Url
    {

        if ( !$this->isUrlShortenerAddress() )
        {
            return null;
        }

        try
        {
            $data = \get_headers( (string) $this, 1 );
            if ( !isset( $data[ 'Location' ] ) )
            {
                return null;
            }
            if ( false === ( $url = Url::Parse( $data[ 'Location' ] ) ) )
            {
                return null;
            }

            return $url;
        }
        catch ( \Throwable $ex )
        {
            unset( $ex );

            return null;
        }

    }

    /**
     * Returns, if the current url contains some GET parameter(s) that are able to be used for the
     * "Open Redirection Bug" (OR-Bug).
     *
     * The OR-Bug can be used to redirect from URL currently not known as bad url, to some bader spaming url
     * (or something else)
     *
     * For doing it, its required to have a bad programmed web application that accepts unchecked GET parameter
     * used to define a redirection target URL. Like
     *
     * <code>http://example.com/?redirect=http%3A%2F%2Fexample.net%2Fbadurl</code>
     *
     * If a possible open redirection URL was found, it is stored as a separate \Niirrty\Web\Url instance and can
     * be accessed by {@see \Niirrty\Web\Url::getOpenRedirectionURLs()}.
     *
     * If it really works as a usable open redirection bug, can only be checked, if a real request is send, to
     * check if the redirection works. If you want to real check it out you can use the
     * {@see \Niirrty\Web\Url::checkForOpenRedirect()} method. But its important to read its documentation before!
     *
     * @param int &$resultPoints returns the max. probability of a URL injection (0-10 and > 4 means its possible)
     *
     * @return boolean
     */
    public function isPossibleOpenRedirect( &$resultPoints ): bool
    {

        // We are working with result points (> 4 returns TRUE)
        // to getting information about the badness (possibility of open redirect) of a url
        $resultPoints = 0;

        if ( \count( $this->openRedirectionURLs ) > 0 )
        {
            // If there are already some check results use it
            $resultPoints = $this->lastOpenRedirectResultPoints;

            // and return the existing result
            return $resultPoints > 4;
        }

        if ( !\is_array( $this->query ) || \count( $this->query ) < 1 )
        {
            // If no query parameters are defined every thing is OK and we do'nt have to do more checks
            return false;
        }

        // Init array to hold some founded param names key) and associated resultpoints
        $founds = [];
        $highest = 0;
        $query = \is_array( $this->query ) ? (array) $this->query : [];

        // OK lets check all GET/query parameters
        foreach ( $query as $key => $value )
        {

            if ( !\is_string( $value ) )
            {
                // The query parameter value is not a string. Go to next one.
                continue;
            }

            if ( !\preg_match( '~^(https?|ftps?)://~i', $value ) )
            {
                // The query parameter value is not a web url, go to next one.
                continue;
            }

            // Getting the URL instance to do some more detailed checks.
            if ( false === ( $url = Url::Parse( $value ) ) )
            {
                continue;
            }

            if ( !( $url->getDomain() instanceof Domain ) )
            {
                // There is no usable domain defined, go to next one.
                continue;
            }

            // Do some Domain specific checks
            if ( ( (string) $url->getDomain() ) === ( (string) $this->getDomain() ) )
            {
                // If it points to the same domain its not problem, go to next one.
                continue;
            }

            if ( ( (string) $url->getDomain()->getSecondLevelDomain() ) ===
                 ( (string) $this->getDomain()->getSecondLevelDomain() ) )
            {
                // If
                $founds[ $key ] = 4;
            }
            else
            {
                $founds[ $key ] = 5;
            }

            if ( \preg_match( '~^(url|redir|addr|loc)~i', $key ) )
            {
                $founds[ $key ] += 2;
            }

            if ( !$url->useAssociatedPort() )
            {
                // Make it bad if no associated Port is used
                ++$founds[ $key ];
            }

            if ( $url->getDomain()->isIPAddress() )
            {
                // Make it bad if a IP address is used.
                ++$founds[ $key ];
            }

            if ( $url->hasLoginData() )
            {
                // Make it bader if a login data are defined by url
                ++$founds[ $key ];
            }

            if ( $url->isUrlShortenerAddress() )
            {
                // Make it more bad if url points to a URL shortener service
                $founds[ $key ] += 2;
            }

            if ( $founds[ $key ] > 10 )
            {
                // Normalize to a maximum value of 10.
                $founds[ $key ] = 10;
            }

            if ( $founds[ $key ] > $highest )
            {
                // Remember the highest value
                $highest = $founds[ $key ];
            }

            if ( $founds[ $key ] > 4 )
            {
                $this->openRedirectionURLs[ $key ] = $url;
            }

        }

        if ( $highest > 4 )
        {
            $resultPoints = $highest;
            $this->lastOpenRedirectResultPoints = $highest;

            return true;
        }

        $this->lastOpenRedirectResultPoints = 0;

        return false;

    }

    /**
     * Returns all possible open redirection URLs, defined if {@see \Niirrty\Web\Url::isPossibleOpenRedirect()}
     * returns TRUE.
     *
     * @return Url[]
     */
    public function getOpenRedirectionURLs()
    {

        return $this->openRedirectionURLs;
    }

    /**
     * Checks, if possible open redirection bug URLs are defined, if one of it its a real open redirection usage.
     *
     * Attention. It sends a real request to each URL. Do'nt use it inside you're main web application because it
     * blocks it as long if it gets a answer. Maybe better use it in cron jobs or inside a very low frequenced area!
     *
     * How it works is easy. All you need is a url and its well known output.
     *
     * If we replace the possible redirection URL inside the current url with the URL where we know the output and
     * it redirects to the url with the known output, the bug is used.
     *
     * @param string  $urlForTestContents The URL with the known output
     * @param string  $testContents       The known output of $urlForTestContents (or a regex if $useAsRegex is TRUE)
     * @param boolean $useAsRegex         Should $testContents be used as a regular expression?
     *
     * @return boolean
     */
    public function checkForOpenRedirect( $urlForTestContents, $testContents, $useAsRegex = false )
    {

        if ( \count( $this->openRedirectionURLs ) < 1 )
        {
            // If no open redirection URLs was found by isPossibleOpenRedirect(…) we are already done here
            return false;
        }

        // Remember the current query parameters
        $oldQuery = $this->query;

        // Getting the query keys
        $keys = \array_keys( $this->openRedirectionURLs );

        // Loop the query keys and assign the replacement url to this query
        foreach ( $keys as $key )
        {
            $this->query[ $key ] = $urlForTestContents;
        }

        // Adjust get_headers() to send a HEAD request
        \stream_context_set_default(
            [
                'http' => [ 'method' => 'HEAD' ],
            ]
        );

        // Getting th URL string to call
        $url = (string) $this;

        // Init state flag
        $handleHeaders = true;

        // OK now we can reassign the origin headers
        $this->query = $oldQuery;

        if ( false === ( $headers = \get_headers( $url, 1 ) ) )
        {

            // If the head request fails get headers from GET request
            \stream_context_set_default(
                [
                    'http' => [ 'method' => 'GET' ],
                ]
            );

            // Get header by GET request
            if ( false === ( $headers = \get_headers( $url, 1 ) ) )
            {
                $handleHeaders = false;
            }

        }
        else
        {

            // reset get_header to use defaut GET request
            \stream_context_set_default(
                [
                    'http' => [ 'method' => 'GET' ],
                ]
            );

        }

        if ( $handleHeaders && \count( $headers ) > 0 )
        {
            // There are usable headers in response, handle it

            // Make header keys to lower case
            $headers = \array_change_key_case( $headers, \CASE_LOWER );

            if ( isset( $headers[ 'location' ] ) && ( $urlForTestContents === $headers[ 'location' ] ) )
            {
                // Location header to defined URL is defined. Now we know its a open redirection bug usage
                return true;
            }

            if ( isset( $headers[ 'refresh' ] ) && strContains( $headers[ 'refresh' ], $urlForTestContents ) )
            {
                // Refresh header to defined URL is defined. Now we know its a open redirection bug usage
                return true;
            }

        }

        // We can not work with headers because they dont gives us the required informations.

        // Get the data from URL to check
        $resultContents = \file_get_contents( $url );
        if ( $useAsRegex )
        {
            try
            {
                return (bool) \preg_match( $testContents, $resultContents );
            }
            catch ( \Throwable $ex )
            {
                unset( $ex );
            }
        }

        $regex = '~<meta\s+http-equiv=(\'|")?refresh(\'|")?\s+content=(\'|")\d+;\s*url='
                 . \preg_quote( $url )
                 . '~i';

        if ( \preg_match( $regex, $resultContents ) )
        {
            return true;
        }

        return $testContents === $resultContents;

    }

    /**
     * Gets if an anchor is defined
     *
     * @return bool
     */
    public function hasAnchor(): bool
    {

        return null !== $this->anchor;

    }

    /**
     * Gets if an query part is defined.
     *
     * @return bool
     */
    public function hasQuery(): bool
    {

        return 0 < \count( $this->query );

    }


    /**
     * Gets the URL scheme. Default is 'http'
     *
     * @return string
     */
    public function getScheme(): string
    {

        if ( null === $this->scheme )
        {
            $this->scheme = static::$fallbackScheme;
        }

        return $this->scheme;

    }

    /**
     * Gets the Domain/host of the URL.
     *
     * @return Domain
     */
    public function getDomain(): Domain
    {

        return $this->domain;

    }

    /**
     * Gets the port if defined/known.
     *
     * @return int
     */
    public function getPort(): int
    {

        if ( null !== $this->port )
        {
            return $this->port;
        }

        if ( null === $this->scheme )
        {
            $this->scheme = static::$fallbackScheme;
        }

        switch ( \strtolower( $this->scheme ) )
        {
            case UrlScheme::HTTP :
                return 80;
            case UrlScheme::SSL  :
                return 443;
            case UrlScheme::FTP  :
                return 21;
            default              :
                return 0;
        }

    }

    /**
     * Gets the optional auth user name part. (Usage is a security issue!)
     *
     * @return null|string
     */
    public function getAuthUser(): ?string
    {

        return $this->authUser;

    }

    /**
     * Gets the optional auth password part. (Usage is a security issue!)
     *
     * @return null|string
     */
    public function getAuthPassword(): ?string
    {

        return $this->authPass;

    }

    /**
     * Gets the path part of the URL. If none is defined, '/' is used and returned.
     *
     * @return string
     */
    public function getPath(): string
    {

        if ( '' === $this->path )
        {
            return '/';
        }

        if ( !strStartsWith( $this->path, '/' ) )
        {
            return '/' . $this->path;
        }

        return $this->path;

    }

    /**
     * Gets the query parameters as string array. If no params are defined, a empty string is returned
     *
     * @return string
     */
    public function getQueryString(): string
    {

        if ( \count( $this->query ) < 1 )
        {
            return '';
        }

        return '?' . \http_build_query( $this->query );

    }

    /**
     * Gets the query parameters as associative array.
     *
     * @return array
     */
    public function getQuery(): array
    {

        return $this->query;

    }

    /**
     * Gets the optional URL anchor name with the leading '#'. If none is defined, a empty string is returned
     *
     * @return string
     */
    public function getAnchor(): string
    {

        return ( null === $this->anchor ) ? '' : ( '#' . $this->anchor );

    }

    /**
     * Sets the URL scheme. Default is 'http' if none is defined
     *
     * @param string|null $scheme
     *
     * @return Url
     */
    public function setScheme( ?string $scheme = null ): Url
    {

        if ( null === $scheme || !\preg_match( '~^[a-z]{3,7}$~i', $scheme ) )
        {
            $this->scheme = 'http';

            return $this;
        }

        $this->scheme = $scheme;

        return $this;

    }

    public function setPort( ?int $port = 0 ): Url
    {

        if ( null === $port || 1 > $port || 65555 < $port )
        {
            $this->port = null;

            return $this;
        }

        $this->port = $port;

        return $this;

    }

    public function setAuthUser( ?string $userName = null ): Url
    {

        if ( null === $userName || '' === $userName )
        {
            $this->authUser = null;

            return $this;
        }

        $this->authUser = \urldecode( $userName );

        return $this;

    }

    public function setAuthPassword( ?string $password = null ): Url
    {

        if ( null === $password || '' === $password )
        {
            $this->authPass = null;

            return $this;
        }

        $this->authPass = \urldecode( $password );

        return $this;

    }

    public function setDomain( Domain $domain ): Url
    {

        $this->domain = $domain;

        return $this;

    }

    public function setPath( ?string $path = null ): Url
    {

        if ( null === $path || '' === $path || !\preg_match( '#^[a-z0-9_.:,@%/+*~$-]+$#i', $path ) )
        {
            $this->path = '/';

            return $this;
        }

        $this->path = '/' . \urldecode( \ltrim( $path, '/' ) );

        return $this;

    }

    public function setQuery( array $query = [] ): Url
    {

        $this->query = $query;

        return $this;

    }

    public function setQueryString( ?string $queryString = null ): Url
    {

        if ( null === $queryString || '' === \trim( $queryString ) )
        {
            $this->query = [];

            return $this;
        }

        \parse_str( $queryString, $output );

        $this->query = \is_array( $output ) ? $output : [];

        return $this;

    }

    public function setAnchor( ?string $anchor = null ): Url
    {

        if ( null === $anchor || '' === $anchor || '#' === $anchor ||
             !\preg_match( '~^#?[a-z_-][a-z0-9_.-]*$~i', $anchor ) )
        {
            $this->anchor = null;

            return $this;
        }

        $this->anchor = \ltrim( $anchor, '#' );

        return $this;

    }

    /**
     * The magic string cast method.
     *
     * @return string
     */
    public function __toString()
    {

        $url = $this->scheme . '://';

        // Add AUTH data if defined
        if ( $this->hasLoginData() )
        {
            if ( null !== $this->authUser )
            {
                $url .= \urlencode( $this->authUser );
            }
            $url .= ':';
            if ( !empty( $this->authPass ) )
            {
                $url .= \urlencode( $this->authPass );
            }
            $url .= '@';
        }

        $url .= $this->domain->toString();
        if ( null !== $this->port )
        {
            $url .= ( ':' . $this->port );
        }

        $url .= ( $this->path . $this->getAnchor() . $this->getQueryString() );

        return $url;

    }

    // </editor-fold>


    // <editor-fold desc="// – – –   P U B L I C   S T A T I C   M E T H O D S   – – – – – – – – – – – – – – – – –">

    /**
     * Finds all valid URLs inside the defined text and returns it as a string array.
     *
     * @param string $text          The text where the URLs should be extracted from
     * @param array  $ignoreDomains Numeric indicated array, defining domains that should be ignored
     *
     * @return array
     */
    public static function FindAllUrls( string $text, array $ignoreDomains = [] )
    {

        // Init the required variables
        $result = [];

        // Find all URL strings
        \preg_match_all( static::URL_FINDER, $text, $matches );


        if ( \count( $matches ) > 0 && \count( $matches[ 0 ] ) > 0 )
        {

            // We have found some URLs, get it

            $matches = (array) $matches[ 0 ];

            // Loop all matching strings
            foreach ( $matches as $match )
            {
                if ( false === ( $url = Url::Parse( $match ) ) )
                {
                    continue;
                }
                if ( \in_array( $url->getDomain()->toString(), $ignoreDomains, true ) ||
                     \in_array( $url->getDomain()->getSecondLevelDomain()->toString(), $ignoreDomains, true ) )
                {
                    continue;
                }
                $result[] = $match;
            }

        }

        // Find all www.* URL without a scheme
        \preg_match_all( '~(?<=\A|\s)www\.([a-z0-9][a-z0-9_./+%?&#-]+)~i', $text, $matches );

        if ( \count( $matches ) > 0 && \count( $matches[ 0 ] ) > 0 )
        {
            foreach ( (array) $matches[ 0 ] as $match )
            {
                if ( false === ( $url = Url::Parse( $match ) ) )
                {
                    continue;
                }
                if ( \in_array( $url->getDomain()->toString(), $ignoreDomains, true ) ||
                     \in_array( $url->getDomain()->getSecondLevelDomain()->toString(), $ignoreDomains, true ) )
                {
                    continue;
                }
                $result[] = 'http://' . $match;
            }
        }

        return $result;

    }

    /**
     * Parses a URL string and returns the resulting {@see \Niirrty\Web\Url} instance. If parsing fails, it returns
     * boolean FALSE.
     *
     * @param string $urlString The URL string to parse
     *
     * @return Url|bool Returns the URL if parsing was successful, FALSE otherwise.
     */
    public static function Parse( ?string $urlString )
    {

        // Null and empty string are not a valid URL
        if ( null === $urlString || '' === $urlString )
        {
            return false;
        }

        if ( !\preg_match( '~^[^:]+://~', $urlString ) &&
             !\preg_match( '~^mailto:[a-z0-9_]~i', $urlString ) )
        {
            // $urlString do not starts with a valid scheme => Append the fallback scheme.
            if ( 'mailto' === static::$fallbackScheme )
            {
                if ( false === MailAddress::Parse( $urlString, false, false, true ) )
                {
                    return false;
                }
                $urlString = 'mailto:' . $urlString;
            }
            else
            {
                $urlString = static::$fallbackScheme . '://' . $urlString;
            }
        }

        // Extract the URL information
        $urlInfo = static::getUrlInfo( $urlString );
        if ( !\is_array( $urlInfo ) || \count( $urlInfo ) < 1 )
        {
            // No arms => no cookies :-(
            return false;
        }

        // Switch the case of the array keys to lower case.
        $objectData = \array_change_key_case( $urlInfo, \CASE_LOWER );

        // The host must be defined!
        if ( empty( $objectData[ 'host' ] ) )
        {
            return false;
        }

        $scheme = static::$fallbackScheme;
        if ( isset( $objectData[ 'scheme' ] ) )
        {
            $scheme = \strtolower( $objectData[ 'scheme' ] );
        }
        $domain = Domain::Parse( $objectData[ 'host' ], false );
        if ( !( $domain instanceof Domain ) )
        {
            // if no usable domain is defined, return FALSE
            return false;
        }

        $url = new Url( $scheme, $domain );
        if ( isset( $objectData[ 'port' ] ) )
        {
            $url->setPort( (int) $objectData[ 'port' ] );
        }
        if ( isset( $objectData[ 'user' ] ) )
        {
            $url->setAuthUser( $objectData[ 'user' ] );
        }
        if ( isset( $objectData[ 'pass' ] ) )
        {
            $url->setAuthPassword( $objectData[ 'pass' ] );
        }
        if ( isset( $objectData[ 'path' ] ) )
        {
            $url->setPath( $objectData[ 'path' ] );
        }
        if ( isset( $objectData[ 'query' ] ) )
        {
            $url->setQuery( static::parseQuery( $objectData[ 'query' ] ) );
        }
        if ( isset( $objectData[ 'fragment' ] ) )
        {
            $url->setAnchor( $objectData[ 'fragment' ] );
        }

        return $url;

    }

    /**
     * UTF-8 aware parse_url() replacement.
     *
     * Returned values can use the following keys (all optionally):
     *
     * - scheme: e.g. http
     * - host
     * - port
     * - user
     * - pass
     * - path
     * - query: after the question mark ?
     * - fragment: after the hashmark #
     *
     * @param string $url
     *
     * @return array|bool Returns the resulting array, or FALSE, if parsing fails
     */
    public static function getUrlInfo( $url )
    {

        // Encode the URL
        /** @noinspection NotOptimalRegularExpressionsInspection */
        $encUrl = \preg_replace_callback(
            '%[^:/@?&=#]+%usD',
            function ( $match )
            {

                return \urlencode( $match[ 0 ] );
            },
            $url
        );
        if ( false === ( $parts = \parse_url( $encUrl ) ) )
        {
            return [];
        }
        foreach ( (array) $parts as $name => $value )
        {
            $parts[ $name ] = \urldecode( $value );
        }

        return $parts;
    }

    // </editor-fold>


    private static function parseQuery( $query )
    {

        if ( empty( $query ) )
        {
            return [];
        }

        $elements = [];

        \parse_str( $query, $elements );

        if ( !\is_array( $elements ) )
        {
            return [];
        }

        return $elements;

    }


}

