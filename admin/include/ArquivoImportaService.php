<?php

use Akeneo\Component\SpreadsheetParser\SpreadsheetParser;

/**
 * Class ArquivoImportaService
 * @package App\Services
 */
class ArquivoImportaService
{
    /**
     * @var string
     */
    protected $folderFiles;

    /**
     * @var array
     */
    protected $statusStrings = [
        \ZipArchive::ER_OK => 'No error',
        \ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported',
        \ZipArchive::ER_RENAME => 'Renaming temporary file failed',
        \ZipArchive::ER_CLOSE => 'Closing zip archive failed',
        \ZipArchive::ER_SEEK => 'Seek error',
        \ZipArchive::ER_READ => 'Read error',
        \ZipArchive::ER_WRITE => 'Write error',
        \ZipArchive::ER_CRC => 'CRC error',
        \ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed',
        \ZipArchive::ER_NOENT => 'No such file',
        \ZipArchive::ER_EXISTS => 'File already exists',
        \ZipArchive::ER_OPEN => 'Can\'t open file',
        \ZipArchive::ER_TMPOPEN => 'Failure to create temporary file',
        \ZipArchive::ER_ZLIB => 'Zlib error',
        \ZipArchive::ER_MEMORY => 'Malloc failure',
        \ZipArchive::ER_CHANGED => 'Entry has been changed',
        \ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported',
        \ZipArchive::ER_EOF => 'Premature EOF',
        \ZipArchive::ER_INVAL => 'Invalid argument',
        \ZipArchive::ER_NOZIP => 'Not a zip archive',
        \ZipArchive::ER_INTERNAL => 'Internal error',
        \ZipArchive::ER_INCONS => 'Zip archive inconsistent',
        \ZipArchive::ER_REMOVE => 'Can\'t remove file',
        \ZipArchive::ER_DELETED => 'Entry has been deleted',
    ];

    /**
     * ArquivoImportaService constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function save()
    {
        if (!isset($_FILES['arquivo'])) {
            throw new \InvalidArgumentException('Nenhum arquivo enviado!');
        }

        $arquivo = $this->upload();

        if (!$arquivo) {
            throw new \InvalidArgumentException('Arquivo não encontrado!');
        }

        return $this->process($arquivo);
    }

    /**
     * @return bool|string
     */
    protected function upload()
    {
        $file = $_FILES['arquivo'];

        // Pega nome do arquivo sem a extensão
        $originalName = $file['name'];
        $originalNameTmp = explode('.', $originalName);
        $extensao = array_pop($originalNameTmp);

        $filename = time() . '_'  . sanitize_title(implode('.', $originalNameTmp)) . '.' . $extensao;
        $filepath = SRM_PAGAMENTO_UPLOAD_DIR;

        if (move_uploaded_file($file['tmp_name'], $filepath . $filename)) {
            return $filepath . $filename;
        }

        return false;
    }

    /**
     * @param $arquivo
     * @return array
     */
    protected function process($arquivo)
    {
        $this->folderFiles = SRM_PAGAMENTO_UPLOAD_DIR . 'descompactadas/' . sha1($arquivo . time()) . '/';

        $zip = new \ZipArchive();

        if ($zip->open($arquivo) !== true) {
            throw new \InvalidArgumentException('Erro ao abrir: ' . $arquivo);
        }

        if (!$this->extract($zip, $this->folderFiles)) {
            throw new \InvalidArgumentException('Erro ao extrair os arquivos.');
        }

        $files = glob($this->folderFiles . '*.xls*');
        if (count($files) === 0) {
            throw new \InvalidArgumentException('Planilha não encontrada.');
        }

        return $this->processSpreadsheets($files);
    }

    /**
     * @param $files
     */
    protected function processSpreadsheets($files)
    {
        $spreadsheet = array_shift($files);
        $workbook = SpreadsheetParser::open($spreadsheet);

        $worksheets = $workbook->getWorksheets();
        $worksheet = array_shift($worksheets);

        $worksheetIndex = $workbook->getWorksheetIndex($worksheet);

        $history = [];
        $initialLineData = 0;
        $users = [];
        $first = true;
        foreach ($workbook->createRowIterator($worksheetIndex) as $rowIndex => $values) {
            if ($first) {
                if (!in_array($values[0], ['Usuario', 'Associado'])) {
                    throw new \InvalidArgumentException('Dados da coluna inicial, da planilha, inválida.');
                }

                $first = false;
                $initialLineData = $rowIndex;
            } else {
                if ($initialLineData !== 0) {
                    try {
                        $imported = $this->importData($values, $users);
                        $history[] = [
                            'data' => $values,
                            'success' => $imported !== false,
                            'id' => $imported,
                            'updated' => $imported === -1,
                        ];
                    } catch(\InvalidArgumentException $e) {
                        $history[] = [
                            'data' => $values,
                            'success' => false,
                            'id' => false,
                            'updated' => false,
                            'error' => $e->getMessage(),
                        ];
                    }
                    continue;
                }
            }
        }

        return $history;
    }

    /**
     * Structure map
     * - 0: Base
     * - 1: Vencimento
     * - 2: Valor
     * - 3: Pagamento
     * - 4: Nome do Arquivo
     *
     * @param $values
     * @return bool
     */
    protected function importData($values, &$users)
    {
        global $wpdb, $srm_pagamentos_tbl_name;

        $values = array_map(function($item) {
            return is_string($item) ? trim($item) : $item;
        }, $values);

        if (empty($values[1])) {
            throw new \InvalidArgumentException('Vencimento não informado');
        }

        if (empty($values[2])) {
            throw new \InvalidArgumentException('Valor não informado.');
        }

        if (empty($values[4])) {
            throw new \InvalidArgumentException('Arquivo não informado.');
        }

        $filePath = $this->folderFiles . $values[4];
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('Arquivo não existe: ' . $values[4]);
        }

        if (!isset($users[$values[0]])) {
            $usuarios = $wpdb->get_results(
                'SELECT * FROM ' . $wpdb->prefix . 'users WHERE ' .
                'user_email = "' . $values[0] . '"'
            );

            if ($wpdb->num_rows == 0) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Nenhum usuário encontrado com o e-mail: %s.',
                        $values[0]
                    )
                );
            }

            $usuario = array_shift($usuarios);
            $users[$values[0]] = $usuario->ID;
        } else {
            $usuario = new stdClass();
            $usuario->ID = $users[$values[0]];
        }

        $entidades = $wpdb->get_results(
            'SELECT * FROM ' . $srm_pagamentos_tbl_name
            . ' WHERE email = "' . $values[0] . '"'
            . ' AND vencimento = "' . $values[1]->format('Y-m-d') . '"'
//            . ' AND valor = ' . $values[2]
        );

        $atualiza = false;
        $entidade = null;
        if ($wpdb->num_rows > 0) {
            $entidade = array_shift($entidades);
            if ($entidade->valor == $values[2]) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Arquivo %s já existe no sistema.',
                        $values[4]
                    )
                );
            }

            $dados = [
                'valor' => $values[2],
            ];
            $atualiza = true;
        } else {
            $dados = [
                'user_id' => $usuario->ID,
                'email' => $values[0],
                'vencimento' => $values[1]->format('Y-m-d'),
                'valor' => $values[2],
            ];
        }

        if (!empty($values[3])) {
            if ($values[3] instanceof \DateTime) {
                $dados['pagamento'] = $values[3]->format('Y-m-d');
                $dados['pago'] = 1;
            }
        }

        $fileTmp = explode('/', $values[4]);
        $filename = time() . '_' . sha1($values[4]) . '_' . sanitize_title(array_pop($fileTmp));
        if(!copy($filePath, SRM_PAGAMENTO_UPLOAD_DIR . 'pagamentos/' . $filename)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Erro ao importar arquivo de "%s" para "%s".',
                    $filePath,
                    SRM_PAGAMENTO_UPLOAD_DIR . 'pagamentos/' . $filename
                )
            );
        }

        $dados['arquivo'] = $filename;

        if ($atualiza && $entidade) {
            $wpdb->update($srm_pagamentos_tbl_name, $dados, ['id' => $entidade->id]);

            return -1;
        } else {
            $wpdb->insert($srm_pagamentos_tbl_name, $dados);

            return $wpdb->insert_id;
        }
    }

    /**
     * @param \ZipArchive $zip
     * @param $targetPath
     * @return array
     */
    protected function extract(\ZipArchive $zip, $targetPath)
    {
        $targetPath = $this->fixPath($targetPath);
        $filenames  = $this->extractFilenames($zip);

        if ($zip->extractTo($targetPath, $filenames) === false) {
            throw new \InvalidArgumentException($this->getError($zip->status));
        }

        $zip->close();

        return $filenames;
    }

    /**
     * @param $path
     * @return string
     */
    protected function fixPath($path)
    {
        if (substr($path, -1) === '/') {
            $path .= '/';
        }

        return $path;
    }

    /**
     * @param \ZipArchive $zipArchive
     * @return array
     */
    protected function extractFilenames(\ZipArchive $zipArchive)
    {
        $filenames = [];
        $fileCount = $zipArchive->numFiles;
        for ($i = 0; $i < $fileCount; $i++) {
            if (($filename = $this->extractFilename($zipArchive, $i)) !== false) {
                $filenames[] = $filename;
            }
        }

        return $filenames;
    }


    /**
     * @param \ZipArchive $zip
     * @param $fileIndex
     * @return bool
     */
    protected function extractFilename(\ZipArchive $zip, $fileIndex)
    {
        $entry = $zip->statIndex($fileIndex);
        $discards = ['__MACOSX', '__MACOSX/'];

        // Remove invalid folders
        $valid = false;
        array_map(function($item) use ($entry, &$valid) {
            if (strpos($entry['name'], $item) === false) {
                $valid = true;
            }
        }, $discards);

        if (!$valid) {
            return false;
        }

        // If contain folder and is contains Windows directory separator,
        // make some checks and transformations
        if (strpos($entry['name'], '\\') !== false) {
            // If this entry is (sub)directory
            if (substr($entry['name'], -1) === '\\'
                && substr_count($entry['name'], '\\') === 1) {
                $new = sanitize_title(substr($entry['name'], 0, -1) . '/');
            } else { // Is this entry is a file
                $new = $entry['name'];
                $extension = substr($new, strrpos($new, '.'));
                $filename = substr($new, 0, strrpos($new, '.'));

                // convert Windows directory separator to Unix style
                $new = sanitize_title(str_replace('\\', '/', $filename)) . $extension;
            }

            if (!$zip->renameName($entry['name'], $new)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Erro ao corrigir nome de arquivo de %s para %s: %s',
                        $entry['name'],
                        $new,
                        $this->getError($zip->status)
                    )
                );
            }

            $entry = $zip->statIndex($fileIndex);
        }

        if ($this->isValidPath($entry['name'])) {
            return $entry['name'];
        }

        throw new \InvalidArgumentException('Caminho inválido do arquivo.');
    }

    /**
     * @param $path
     * @return bool
     */
    protected function isValidPath($path)
    {
        $pathParts = explode('/', $path);
        if (!strncmp($path, '/', 1) ||
            array_search('..', $pathParts) !== false ||
            strpos($path, ':') !== false)
        {
            return false;
        }

        return true;
    }

    /**
     * @param $status
     * @return string
     */
    protected function getError($status)
    {
        $statusString = isset($this->statusStrings[$status])
            ? $this->statusStrings[$status]
            : 'Unknown status';

        return $statusString . ' (' . $status . ')';
    }
}