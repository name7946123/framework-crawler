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

class CrawlVariableCommand extends Command
{
// the name of the command (the part after "bin/console")
    protected static $defaultName = 'crawler:crawl-variable';

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
            ->setDescription('爬蟲:專爬變數名稱')

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
            $variables = $this->getVariable($content);

            foreach ($variables as $variable) {

                $array = $this->formatToArray($variable);

                if (!empty($array)) {
                    foreach ($array as $value) {
                        $tempInput['value'] = $value;
                        $tempInput['from'] = 'variable';
                        $output->writeln([
                            '--------',
                            '檔案位置：'.$file->getRealPath()
                        ]);
                        $this->wordRepository->createOrUpdate($tempInput);
                        $output->writeln([
                            '儲存成功'.$value,
                            '--------'
                        ]);
                    }
                }

                unset($array);
            }

            $count ++;
            $output->writeln('目前完成：'.$count);
        }

        $output->writeln([
            'Framework Crawler',
            '======結束======',
            date('Y-m-d H:i:s')
        ]);

        $output->writeln('...........................                              
░░░▐▀▀▄█▀▀▀▀▀▒▄▒▀▌░░░░
░░░▐▒█▀▒▒▒▒▒▒▒▒▀█░░░░░
░░░░█▒▒▒▒▒▒▒▒▒▒▒▀▌░░░░
░░░░▌▒██▒▒▒▒██▒▒▒▐░░░░
░░░░▌▒▒▄▒██▒▄▄▒▒▒▐░░░░
░░░▐▒▒▒▀▄█▀█▄▀▒▒▒▒█▄░░
░░░▀█▄▒▒▐▐▄▌▌▒▒▄▐▄▐░░░
░░▄▀▒▒▄▒▒▀▀▀▒▒▒▒▀▒▀▄░░
░░█▒▀█▀▌▒▒▒▒▒▄▄▄▐▒▒▐░░
░░░▀▄▄▌▌▒▒▒▒▐▒▒▒▀▒▒▐░░
░░░░░░░▐▌▒▒▒▒▀▄▄▄▄▄▀░░
░░░░░░░░▐▄▒▒▒▒▒▒▒▒▐░░░
░░░░░░░░▌▒▒▒▒▄▄▒▒▒▐░░░
---------------------------------------------------------');
    }

    /**
     * 取得檔案內所有的變數
     *
     * @param string $content
     *
     * @return array
     */
    private function getVariable($content)
    {
        preg_match_all('/\$[\w]*\b/', $content, $matches, PREG_SET_ORDER);

        $result = [];

        if (!empty($matches)) {
            foreach ($matches as $match) {
                if (!empty($match[0])) {
                    $result[] = $this->removeDollarIcon($match[0]);
                }
            }
            $matches = null;
        }

        return $result;
    }

    /**
     * 去除錢字號
     *
     * @param string $string
     *
     * @return string
     */
    private function removeDollarIcon($string)
    {
        $array = explode('$', $string);
        $result = (!empty($array[0])) ? $array[0] : $array[1];

        return $result;
    }

    /**
     * 拆解文字
     *
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

        $matches = null;

        return $result;
    }
}
