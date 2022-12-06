<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <title>Document</title>
</head>
<body>
    <div class="container">
        <div class="row m-3" style="background-color:#383d52;border-radius:4px">
            <div class="col-lg-12 text-light p-4">
                <h4 class="text-center">{{ $title }}</h4>
                <code>{{ $content }}</code>
                <table class="table table-bordered mt-2">
                    <thead class="text-light">
                        <tr>
                            <th>Key</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody class="text-light">
                        @foreach($data as $key=>$item)
                        <tr>
                            <td>{{ $key }}</td>
                            <td>
                                @if(is_array($item))
                                    <table class="table table-bordered mt-2">
                                        <thead class="text-light">
                                            <tr>
                                                <th>Key</th>
                                                <th>Value</th>
                                            </tr>
                                            <tbody class="text-light">
                                                @foreach($item as  $subItem)
                                                    @foreach($subItem as $subKey => $superSubItem)
                                                    <tr>
                                                        <td>{{ $subKey }}</td>
                                                        <td>
                                                            @if(!is_array($superSubItem))
                                                                {{ $superSubItem }}
                                                            @endif
                                                            @if(is_array($superSubItem))
                                                                <table class="table table-bordered mt-2">   
                                                                    <thead class="text-light">
                                                                        <tr>
                                                                            <th>Key</th>
                                                                            <th>Value</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="text-light">
                                                                        @foreach($superSubItem as $superSubItem_item)
                                                                            @foreach($superSubItem_item as $superSubItem_item_key => $superSubItem_item_item)
                                                                            <tr>
                                                                                <td>{{ $superSubItem_item_key }}</td>
                                                                                <td>
                                                                                    @if(!is_array($superSubItem_item_item))
                                                                                        {{ $superSubItem_item_item }}
                                                                                    @endif
                                                                                    @if(is_array($superSubItem_item_item))
                                                                                    <table class="table table-bordered mt-2">   
                                                                                        <thead class="text-light">
                                                                                            <tr>
                                                                                                <th>Key</th>
                                                                                                <th>Value</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody class="text-light">
                                                                                            @foreach($superSubItem_item_item as $superSubItem_item_item_item)
                                                                                                @foreach($superSubItem_item_item_item as $superSubItem_item_item_item_key => $superSubItem_item_item_item_item)
                                                                                                    <tr>
                                                                                                        <td>{{ $superSubItem_item_item_item_key }}</td>
                                                                                                        <td>{{ $superSubItem_item_item_item_item }}</td>
                                                                                                    </tr>
                                                                                                @endforeach
                                                                                               
                                                                                            @endforeach

                                                                                        </tbody>
                                                                                    </table>
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                            
                                                                            @endforeach
                                                                            <tr>
                                                                                <td colspan="2"><div class="w-100 bg-warning" style="height:3px"></div></td>
                                                                            </tr>
                                                                        @endforeach
                                                                        
                                                                    </tbody>
                                                                </table>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                    <tr>
                                                        <td colspan="2"><div class="w-100 bg-warning" style="height:3px"></div></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </thead>
                                    </table>
                                @endif
                                @if(!is_array($item))
                                    {{ $item }}
                                @endif

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>