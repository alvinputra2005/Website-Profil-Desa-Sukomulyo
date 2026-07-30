@props(['columns' => [], 'leading' => [], 'trailing' => []])

@php($headerRows = \App\Support\StatisticTableHeader::make($columns))
<thead>
    @foreach ($headerRows as $headerRow)
        <tr>
            @if ($loop->first)
                @foreach ($leading as $cell)
                    <th
                        scope="col"
                        rowspan="{{ count($headerRows) }}"
                        @class([$cell['class'] ?? ''])
                    >{{ $cell['label'] }}</th>
                @endforeach
            @endif
            @foreach ($headerRow as $cell)
                <th
                    scope="col"
                    @if ($cell['colspan'] > 1) colspan="{{ $cell['colspan'] }}" @endif
                    @if ($cell['rowspan'] > 1) rowspan="{{ $cell['rowspan'] }}" @endif
                >{{ $cell['label'] }}</th>
            @endforeach
            @if ($loop->first)
                @foreach ($trailing as $cell)
                    <th
                        scope="col"
                        rowspan="{{ count($headerRows) }}"
                        @class([$cell['class'] ?? ''])
                    >{{ $cell['label'] }}</th>
                @endforeach
            @endif
        </tr>
    @endforeach
</thead>
