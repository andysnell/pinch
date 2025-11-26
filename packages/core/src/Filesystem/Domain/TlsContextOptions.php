<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Filesystem\Domain;

/**
 * Immutable Value Object for the TLS/SSL Context Options used for the ssl://,
 * tls://, https://, and ftps:// stream wrappers protocols
 *
 * @link https://www.php.net/manual/en/context.ssl.php
 */
final readonly class TlsContextOptions
{
    /**
     * @param string|null $peer_name Peer name to be used. If this value is not set, then the name is guessed based on
     * the hostname used when opening the stream.
     *
     * @param bool|null $verify_peer Require verification of the SSL certificate used. Defaults to `true`.
     *
     * @param bool|null $verify_peer_name Require verification of the peer name. Defaults to `true`.
     *
     * @param bool|null $allow_self_signed Allow self-signed certificates. Requires verify_peer. Defaults to `false`
     *
     * @param string|null $cafile Location of the Certificate Authority file on the local filesystem which should be
     * used with the verify_peer context option to authenticate the identity of the remote peer.
     *
     * @param string|null $capath If cafile is not specified or if the certificate is not found there, the directory
     * pointed to by capath is searched for a suitable certificate. Must be a correctly hashed certificate directory.
     *
     * @param string|null $local_cert Path to the local certificate file on filesystem. It must be a PEM encoded file
     * which contains your certificate and private key. It can optionally contain the certificate chain of issuers. The
     * private key also may be contained in a separate file specified by local_pk.
     *
     * @param string|null $local_pk Path to the local private key file on filesystem in case of separate files for
     * certificate (local_cert) and private key.
     *
     * @param string|null $passphrase Passphrase with which your local_cert file was encoded.
     *
     * @param int|null $verify_depth Abort if the certificate chain is too deep. Defaults to no verification.
     *
     * @param string|null $ciphers Sets the list of available ciphers. The format of the string is described in
     * https://www.openssl.org/docs/manmaster/man1/ciphers.html#CIPHER-LIST-FORMAT. Defaults to DEFAULT.
     *
     * @param bool|null $capture_peer_cert If set to `true` a peer_certificate context option will be created containing
     * the peer certificate.
     *
     * @param bool|null $capture_peer_cert_chain If set to `true` a peer_certificate_chain context option will be created
     * containing the certificate chain.
     *
     * @param bool|null $SNI_enabled If set to `true` server name indication will be enabled. Enabling SNI allows
     * multiple certificates on the same IP address. Requires that PHP must be compiled with OpenSSL 0.9.8j or greater.
     * Check the OPENSSL_TLSEXT_SERVER_NAME constant value to determine if SNI is supported.
     *
     * @param bool|null $disable_compression If set, disable TLS compression. This can help mitigate the CRIME attack vector.
     *
     * @param string|array<string,string>|null $peer_fingerprint Aborts when the remote certificate digest doesn't match
     * the specified hash. When a string is used, the length will determine which hashing algorithm is applied, either
     * "md5" (32) or "sha1" (40). When an array is used, the keys indicate the hashing algorithm name and each
     * corresponding value is the expected digest.
     *
     * @param int|null $security_level Sets the security level. If not specified, the library default security level
     * is used. The security levels are described in https://www.openssl.org/docs/man1.1.1/man3/SSL_CTX_get_security_level.html
     */
    public function __construct(
        public string|null $peer_name = null,
        public bool|null $verify_peer = null,
        public bool|null $verify_peer_name = null,
        public bool|null $allow_self_signed = null,
        public string|null $cafile = null,
        public string|null $capath = null,
        public string|null $local_cert = null,
        public string|null $local_pk = null,
        #[\SensitiveParameter] public string|null $passphrase = null,
        public int|null $verify_depth = null,
        public string|null $ciphers = null,
        public bool|null $capture_peer_cert = null,
        public bool|null $capture_peer_cert_chain = null,
        public bool|null $SNI_enabled = null,
        public bool|null $disable_compression = null,
        public string|array|null $peer_fingerprint = null,
        public int|null $security_level = null,
    ) {
    }

    public function toArray(): array
    {
        return \array_filter([
            'peer_name' => $this->peer_name,
            'verify_peer' => $this->verify_peer,
            'verify_peer_name' => $this->verify_peer_name,
            'allow_self_signed' => $this->allow_self_signed,
            'cafile' => $this->cafile,
            'capath' => $this->capath,
            'local_cert' => $this->local_cert,
            'local_pk' => $this->local_pk,
            'passphrase' => $this->passphrase,
            'verify_depth' => $this->verify_depth,
            'ciphers' => $this->ciphers,
            'capture_peer_cert' => $this->capture_peer_cert,
            'capture_peer_cert_chain' => $this->capture_peer_cert_chain,
            'SNI_enabled' => $this->SNI_enabled,
            'disable_compression' => $this->disable_compression,
            'peer_fingerprint' => $this->peer_fingerprint,
            'security_level' => $this->security_level,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(#[\SensitiveParameter] array $data): void
    {
        $this->__construct(...$data);
    }
}
