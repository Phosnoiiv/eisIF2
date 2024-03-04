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
        $this->addOption('member', null, InputOption::VALUE_REQUIRED,
            'Fetch "Best Girl Ranking" instead of default "Event pt Ranking". Require Member ID.');
        $this->addOption('score', null, InputOption::VALUE_NONE,
            'Fetch "Event Song Score Rank" instead of default "Event pt Ranking".');
        $this->addOption('start', null, InputOption::VALUE_OPTIONAL, '', 1);
        $this->addOption('end', null, InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->setLoggerConsoleOutput($output);
        $eventId = $input->getArgument('eventId');
        $memberId = $input->getOption('member') ?: 0;
        $isScoreRanking = $input->getOption('score');
        $start = $input->getOption('start');
        $end = $input->getOption('end');
        if ($memberId == 'all') {
            foreach (array_merge(
                range(1001, 1009), range(2001, 2009),
                range(3001, 3012), range(4001, 4011),
            ) as $memberId) {
                $this->fetchRanking($eventId, RankingType::Member, $memberId, $start, $end);
            }
        } else {
            $this->fetchRanking($eventId, match (true) {
                !empty($memberId) => RankingType::Member,
                $isScoreRanking => RankingType::Score,
                default => RankingType::Point,
            }, $memberId, $start, $end);
        }
        return Command::SUCCESS;
    }

    private function fetchRanking(int $eventId, RankingType $type, int $memberId, int $start, ?int $end): void {
        $results = [];
        while (true) {
            $list = $this->flient->getEventRanking($eventId, $type, $start, $memberId);
            if (empty($list)) break;
            foreach ($list as $detail) {
                if (!empty($end) && $detail->rank > $end) break 2;
                $results[] = $this->convertData($detail);
                if (count($results) >= 10000) {
                    $this->interactionStorage->writeRankings($eventId, $results, $memberId);
                    $results = [];
                }
            }
            $this->logger?->info(sprintf('Fetched ranking %d-%d%s',
                $list[0]->rank, $list[array_key_last($list)]->rank,
                $memberId ? ' in group ' . $memberId : '',
            ));
            sleep(1);
            $start += Flient::SIZE_EVENT_RANKING;
        }
        $this->interactionStorage->writeRankings($eventId, $results, $memberId);
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
