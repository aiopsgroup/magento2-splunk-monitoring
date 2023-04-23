<?php

namespace Aiops\Monitoring\Model\Config\Source;

class Schedule implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => '17 minutes', 'label' => __('15 Minute')],
            ['value' => '32 minutes', 'label' => __('30 Minute')],
            ['value' => '47 minutes', 'label' => __('45 Minute')],
            ['value' => '62 minutes', 'label' => __('1 Hour')]
        ];
    }
}
