<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\Authorization\Roles\EventRole;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @property-read int $fyziklani_team_member_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read int $fyziklani_team_id
 * @property-read TeamModel2 $fyziklani_team
 */
final class TeamMemberModel extends Model implements EventRole
{
    public const RoleId = 'event.teamMember';// phpcs:ignore

    public function getPersonHistory(): ?PersonHistoryModel
    {
        return $this->person->getHistory($this->fyziklani_team->event->getContestYear());
    }

    /**
     * @phpstan-return array{
     *     memberId:int,
     *     personId:int,
     *     code: string|null,
     * }
     */
    public function __toArray(): array
    {
        return [
            'memberId' => $this->fyziklani_team_member_id,
            'personId' => $this->person_id,
            'code' => $this->createMachineCode(),
        ];
    }

    /**
     * @throws \DOMException
     */
    public function createXMLNode(\DOMDocument $document): \DOMElement
    {
        $node = $document->createElement('participant');
        $node->setAttribute('eventParticipantId', (string)$this->fyziklani_team_member_id);
        XMLHelper::fillArrayToNode([
            'participantId' => $this->fyziklani_team_member_id,
            'personId' => $this->person_id,
        ], $document, $node);
        return $node;
    }

    public function createMachineCode(): ?string
    {
        try {
            return MachineCode::createModelHash($this->person, $this->fyziklani_team->event->getSalt());
        } catch (\Throwable $exception) {
            return null;
        }
    }

    public function getEvent(): EventModel
    {
        return $this->fyziklani_team->event;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-color-9'])
            ->addText(_('Member') . ': ')
            ->addHtml(
                Html::el('i')->addAttributes(
                    ['class' => $this->fyziklani_team->scholarship->getIconName() . ' me-1']
                )
            )
            ->addText(
                sprintf(
                    '%s (%s)',
                    $this->fyziklani_team->name,
                    $this->fyziklani_team->state->label()
                )
            );
    }

    public function getRoleId(): string
    {
        return self::RoleId;
    }
}
