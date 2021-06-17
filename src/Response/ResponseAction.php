<?php

namespace Symfox\Response;

use Dompdf\Dompdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ResponseAction implements ResponseInterface {

    private $response;

    public function __construct()
    {
        $this->response = new Response(); 
    }

    public function output ($content = null, $type = null) 
    {
        if ($type == 'json') {
            $this->response->setContent(json_encode($content));
        } else {
            $this->response->setContent($content);
        }
        $this->setContentType($type);
        return $this->response;
    }

    public function json ($content = null) 
    {
        $this->response->setContent(json_encode($content));
        $this->setContentType('json'); 
        return $this->response;
    }

    public function pdf ($content = null) 
    {
        $dompdf = new Dompdf();
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($content);
        return $dompdf->output();
    }

    public function csv ($collection, $structure, $path) 
    {    
        $fp = fopen($path, 'w+');  
        fputcsv($fp, $structure[0]); 
        if (! empty($collection)) {
            foreach ($collection as $key => $result) {
                $test_ary = array();
                foreach ($structure[0] as $i => $label) {
                    if ($structure[1][$i] == '__i') {
                        $test_ary[$label] = ++$key;
                    } else {
                        $test_ary[$label] = isset($result->{$structure[1][$i]}) ? $result->{$structure[1][$i]} : 'N/A';
                    }  
                } 
                fputcsv($fp, $test_ary);
                unset($test_ary);
            } 
        }  
        fclose($fp);
    }

    public function exportToCsv ($collection, $structure, $path, $name = 'document') 
    {    
        $fp = fopen($path, 'w+'); 
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$name.'.csv');
        fputcsv($fp, $structure[0]); 
        if (! empty($collection)) {
            foreach ($collection as $key => $result) {
                $test_ary = array();
                if ($structure[1][$key] == '__i') {
                    $test_ary[$structure[0][$key]] = ++$key;
                } else {
                    $test_ary[$structure[0][$key]] = isset($result->{$structure[1][$key]}) ? $result->{$structure[1][$key]} : 'N/A';
                }  
                fputcsv($fp, $test_ary);
                unset($test_ary);
            } 
        }  
        fclose($fp);
    }

    public function redirectTo($url) 
    {
        $this->response = new RedirectResponse($url);
        return $this->response;
    }

    private function setContentType($type = null) 
    {
        if (empty($type)) { 
            $this->response->headers->set('Content-Type', 'text/html');
        } else {
            if ($type = 'json') {
                $this->response->headers->set('Content-Type', 'application/json');
            } else if ($type = 'html') {
                $this->response->headers->set('Content-Type', 'text/html');
            } else {
                $this->response->headers->set('Content-Type', 'text/html');
            }
        } 
    }
}