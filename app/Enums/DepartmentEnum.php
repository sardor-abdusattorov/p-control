<?php

namespace App\Enums;

enum DepartmentEnum: string
{
    case PRESS_SERVICE = 'Пресс служба';
    case CULTURAL_COOPERATION = 'Культурное сотрудничество';
    case IT_DEPARTMENT = 'Отдел обеспечения функционирования ЕЭСБО и развития ИТ';
    case CSI = 'ЦСИ';
    case CREATIVE_PROJECTS = 'Группа креативных проектов и фандрайзинга';
    case LEADERSHIP = 'Руководство';
    case LEGAL = 'Юротдел';
    case FINANCIAL = 'Финансовый отдел';
    case ACCOUNTING = 'Бухгалтерия';
    case SPECIAL_PROJECTS = 'Группа специальных проектов';

    public static function names(): array
    {
        return array_column(self::cases(), 'value');
    }
}
