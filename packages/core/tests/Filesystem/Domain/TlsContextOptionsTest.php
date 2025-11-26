<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Filesystem\Domain;

use PhoneBurner\Pinch\Filesystem\Domain\TlsContextOptions;
use PhoneBurner\Pinch\Random\Randomizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TlsContextOptionsTest extends TestCase
{
    #[Test]
    public function emptyObjectPath(): void
    {
        $options = new TlsContextOptions();
        self::assertSame([], $options->toArray());
        self::assertEquals($options, \unserialize(\serialize($options)));
    }

    #[Test]
    public function happyObjectPath(): void
    {
        $randomizer = new Randomizer();
        $peer_name = 'localhost';
        $verify_peer = $randomizer->bool();
        $verify_peer_name = $randomizer->bool();
        $allow_self_signed = $randomizer->bool();
        $cafile = '/path/to/cafile';
        $capath = '/path/to/capath';
        $local_cert = '/path/to/local_cert';
        $local_pk = '/path/to/local_pk';
        $passphrase = 'the_passphrase';
        $verify_depth = $randomizer->int(0, 10);
        $ciphers = 'ALL';
        $capture_peer_cert = $randomizer->bool();
        $capture_peer_cert_chain = $randomizer->bool();
        $SNI_enabled = $randomizer->bool();
        $disable_compression = $randomizer->bool();
        $peer_fingerprint = 'sha256';
        $security_level = $randomizer->int(0, 10);

        $options = new TlsContextOptions(
            $peer_name,
            $verify_peer,
            $verify_peer_name,
            $allow_self_signed,
            $cafile,
            $capath,
            $local_cert,
            $local_pk,
            $passphrase,
            $verify_depth,
            $ciphers,
            $capture_peer_cert,
            $capture_peer_cert_chain,
            $SNI_enabled,
            $disable_compression,
            $peer_fingerprint,
            $security_level,
        );

        self::assertSame([
            'peer_name' => $peer_name,
            'verify_peer' => $verify_peer,
            'verify_peer_name' => $verify_peer_name,
            'allow_self_signed' => $allow_self_signed,
            'cafile' => $cafile,
            'capath' => $capath,
            'local_cert' => $local_cert,
            'local_pk' => $local_pk,
            'passphrase' => $passphrase,
            'verify_depth' => $verify_depth,
            'ciphers' => $ciphers,
            'capture_peer_cert' => $capture_peer_cert,
            'capture_peer_cert_chain' => $capture_peer_cert_chain,
            'SNI_enabled' => $SNI_enabled,
            'disable_compression' => $disable_compression,
            'peer_fingerprint' => $peer_fingerprint,
            'security_level' => $security_level,
        ], $options->toArray());

        self::assertSame($peer_name, $options->peer_name);
        self::assertSame($verify_peer, $options->verify_peer);
        self::assertSame($verify_peer_name, $options->verify_peer_name);
        self::assertSame($allow_self_signed, $options->allow_self_signed);
        self::assertSame($cafile, $options->cafile);
        self::assertSame($capath, $options->capath);
        self::assertSame($local_cert, $options->local_cert);
        self::assertSame($local_pk, $options->local_pk);
        self::assertSame($passphrase, $options->passphrase);
        self::assertSame($verify_depth, $options->verify_depth);
        self::assertSame($ciphers, $options->ciphers);
        self::assertSame($capture_peer_cert, $options->capture_peer_cert);
        self::assertSame($capture_peer_cert_chain, $options->capture_peer_cert_chain);
        self::assertSame($SNI_enabled, $options->SNI_enabled);
        self::assertSame($disable_compression, $options->disable_compression);
        self::assertSame($peer_fingerprint, $options->peer_fingerprint);
        self::assertSame($security_level, $options->security_level);
        self::assertEquals($options, \unserialize(\serialize($options)));
    }
}
