<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


use Symfony\Component\Finder\Finder;
use ReflectionClass;
use App\Repository\WordRepository;
use ReflectionException;

class CrawlMethodCommand extends Command
{
// the name of the command (the part after "bin/console")
    protected static $defaultName = 'crawler:crawl-method';

    protected $wordRepository;

    public function __construct(WordRepository $wordRepository)
    {
        $this->wordRepository = $wordRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('爬蟲:專爬Method')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('這個command，可以帶你享受爬Method的快感。')
        ;

        // configure an argument
        $this->addArgument('target-path', InputArgument::REQUIRED, '需要給予目標路徑。 ex: D:\wegames_projects\www-wg-v2\core');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetPath = $input->getArgument('target-path');
        $output->writeln([
            'Framework Crawler',
            '======開始======',
            '',
        ]);
        $output->writeln('Whoa!');
        $output->writeln('路徑：' . $targetPath);

        $finder = new Finder();
        $finder->files()->in($targetPath)->name('/\.php$/');
        $count = 0;

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealPath());
            $output->writeln('檔案名稱：'.$file->getFilename());
            $methods = $this->getFunctionName($content);

            foreach ($methods as $method) {
                preg_match_all('/[^\_]+/', $method, $specialWord);

                if (!empty($specialWord[0])) {
                    foreach ($specialWord[0] as $key => $simpleWord) {
                        if ($key != 0) {
                            $specialWord[0][$key] = ucwords($simpleWord);
                        }
                    }
                    $name = implode("", $specialWord[0]);
                    $inputName = $name;

                } else {
                    $inputName = $method;
                }

                $array = $this->formatToArray($inputName);

                foreach ($array as $value) {
                    $tempInput['value'] = $value;
                    $tempInput['from'] = 'method';
                    $this->wordRepository->createOrUpdate($tempInput);
                }
            }

            $count ++;
            $output->writeln('目前完成：'.$count);
        }

        $output->writeln([
            'Framework Crawler',
            '======結束======',
            date('Y-m-d H:i:s')
        ]);
    }

    /**
     * @param string $content
     *
     * @return array
     */
    private function getFunctionName($content)
    {
        preg_match_all('/function[\s+](\S*)\b\(/', $content, $matches, PREG_SET_ORDER);
        $result = [];

        if (!empty($matches)) {
            foreach ($matches as $match) {
                if (!empty($match) && !empty($match[1])) {
                    $result[] = $match[1];
                }
            }
        }

        return $result;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function getFileName($string)
    {
        $array = explode('.php', $string);
        $result = (isset($array[0])) ? $array[0] : $array;

        return $result;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    private function getNameSpace($content)
    {
        preg_match('/namespace[\s\n]+(\S+)[\s\n]/', $content, $match);

        if (!empty($match)) {
            $nameSpace = (isset($match[1])) ? $match[1] : $match[0];
            $array = explode(';', $nameSpace);
            $result = (isset($array[0])) ? $array[0] : $array;
            return $result;
        }

        return '';
    }

    /**
     * @param string $string
     *
     * @return array
     */
    private function formatToArray($string)
    {
        $matches = preg_split('/((?:^|[A-Z])[a-z]+)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = [];

        foreach ($matches as $match) {
            if (!empty($match)) {
                $result[] = strtolower($match);
            }
        }

        return $result;
    }

    /**
     * @param string $filename
     * @param string $content
     *
     * @return bool
     */
    private function isClassFile($filename, $content)
    {
        $pattern = '/class+[\s]+'.$filename.'/';
        preg_match($pattern, $content, $match);
        $result = true;

        if (empty($match)) {
            $result = false;
        }

        return $result;
    }
}
