<?php
namespace EverISay\SIF\ML\Updater\Command;

use EverISay\SIF\ML\Flient\Flient;
use EverISay\SIF\ML\Shared\Enum\RankingType;
use EverISay\SIF\ML\Shared\Response\Data\Element\RankingDetail;
use EverISay\SIF\ML\Storage\Interaction\Ranking;
use EverISay\SIF\ML\Storage\InteractionStorage;
use EverISay\SIF\ML\Updater\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('ranking')]
final class FetchRankingCommand extends Command implements LoggerAwareInterface {
    use LoggerAwareTrait;

    function __construct(
        private readonly Flient $flient,
        private readonly InteractionStorage $interactionStorage,
    ) {
        parent::__construct();
        $this->addArgument('eventId', InputArgument::REQUIRED);
        $this->addOption('start', null, InputOption::VALUE_OPTIONAL, '', 1);
        $this->addOption('end', null, InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->setLoggerConsoleOutput($output);
        $eventId = $input->getArgument('eventId');
        $start = $input->getOption('start');
        $end = $input->getOption('end');
        $results = [];
        while (true) {
            $list = $this->flient->getEventRanking($eventId, RankingType::Point, $start);
            if (empty($list)) break;
            foreach ($list as $detail) {
                if (!empty($end) && $detail->rank > $end) break 2;
                $results[] = $this->convertData($detail);
                if (count($results) >= 10000) {
                    $this->interactionStorage->writeRankings($eventId, $results);
                    $results = [];
                }
            }
            $this->logger?->info('Fetched ranking ' . $list[0]->rank . '-' . $list[array_key_last($list)]->rank);
            sleep(1);
            $start += Flient::SIZE_EVENT_RANKING;
        }
        $this->interactionStorage->writeRankings($eventId, $results);
        return Command::SUCCESS;
    }

    private function convertData(RankingDetail $detail): Ranking {
        return new Ranking(
            rank: $detail->rank,
            starLevel: $detail->starLevel ?? 0,
            score: $detail->score,
            userId: $detail->userDetail->user->id,
            userName: $detail->userDetail->user->name,
            userComment: $detail->userDetail->user->comment,
            userExp: $detail->userDetail->user->exp,
            favoriteCardId: $detail->userDetail->user->favoriteMasterCardId,
            favoriteCardEvolve: $detail->userDetail->user->favoriteCardEvolve,
            favoriteCardExp: $detail->userDetail->favoriteCard->exp,
            favoriteCardSkillExp: $detail->userDetail->favoriteCard->skillExp,
            favoriteCardCreateTime: $detail->userDetail->favoriteCard->createdDateTime,
            guestSmileCardId: $detail->userDetail->user->guestSmileMasterCardId,
            guestCoolCardId: $detail->userDetail->user->guestCoolMasterCardId,
            guestPureCardId: $detail->userDetail->user->guestPureMasterCardId,
            titleId: $detail->userDetail->user->masterTitleIds[0],
            lastLoginTime: $detail->userDetail->user->lastLoginTime,
        );
    }
}
