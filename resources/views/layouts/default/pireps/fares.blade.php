@if($aircraft)
    <div class="form-container">
        <h6><i class="fas fa-ellipsis-h"></i>
            &nbsp;{{ trans_choice('pireps.fare', 2) }}
        </h6>
        <div class="form-container-body">
            @foreach($aircraft->subfleet->fares as $fare)
                <div class="row">
                    <div class="col">
                        {{Form::label('fare_'.$fare->id, $fare->name.' ('.$fare->code.')')}}
                        <div class="input-group form-group">
                            {{ Form::number('fare_'.$fare->id, null, ['class' => 'form-control', 'min' => 0]) }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
