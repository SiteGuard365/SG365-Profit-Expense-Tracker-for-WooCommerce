<?php
/**
 * Very lightweight PDF exporter.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Export_PDF {

    /**
     * Generate PDF.
     *
     * @param string $report Report.
     * @param string $from From.
     * @param string $to To.
     * @return array<string, string>|WP_Error
     */
    public static function generate( string $report, string $from, string $to ) {
        $rows = WCPI_Export_CSV::build_rows( $report, $from, $to );
        if ( empty( $rows ) ) {
            return new WP_Error( 'wcpi_empty_export', __( 'No data available for export.', WCPI_TEXT_DOMAIN ) );
        }

        $text_lines = array();
        foreach ( $rows as $row ) {
            $text_lines[] = implode( ' | ', array_map( 'wp_strip_all_tags', array_map( 'strval', $row ) ) );
        }

        $pdf = self::build_minimal_pdf( $text_lines );
        $filename = WCPI_Filesystem::secure_filename( 'wcpi-' . $report, 'pdf' );
        return WCPI_Filesystem::write_file( 'exports', $filename, $pdf );
    }

    /**
     * Build a tiny valid PDF from text lines.
     *
     * @param array<int, string> $lines Lines.
     * @return string
     */
    private static function build_minimal_pdf( array $lines ): string {
        $safe_lines = array_map(
            static function ( $line ) {
                $line = substr( preg_replace( '/[^\x20-\x7E]/', '', $line ), 0, 120 );
                return str_replace( array( '\\', '(', ')' ), array( '\\\\', '\\(', '\\)' ), $line );
            },
            $lines
        );

        $y = 780;
        $stream = "BT\n/F1 10 Tf\n50 {$y} Td\n";
        foreach ( $safe_lines as $index => $line ) {
            if ( 0 === $index ) {
                $stream .= '(' . $line . ") Tj\n";
            } else {
                $stream .= "0 -14 Td\n(" . $line . ") Tj\n";
            }
        }
        $stream .= "ET";

        $objects   = array();
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>";
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[] = "<< /Length " . strlen( $stream ) . " >>\nstream\n" . $stream . "\nendstream";

        $pdf     = "%PDF-1.4\n";
        $offsets = array( 0 );

        foreach ( $objects as $i => $object ) {
            $offsets[] = strlen( $pdf );
            $pdf      .= ( $i + 1 ) . " 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen( $pdf );
        $pdf .= "xref\n0 " . ( count( $objects ) + 1 ) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ( $i = 1; $i <= count( $objects ); $i++ ) {
            $pdf .= sprintf( "%010d 00000 n \n", $offsets[ $i ] );
        }

        $pdf .= "trailer\n<< /Root 1 0 R /Size " . ( count( $objects ) + 1 ) . " >>\nstartxref\n{$xref}\n%%EOF";
        return $pdf;
    }
}
