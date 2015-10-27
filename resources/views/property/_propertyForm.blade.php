<div class="container">
  @if(isset($property))
    {!! Form::model($property, ['url' => route('property-edit-action', ['id' => $property->id])]) !!}
  @else
    {!! Form::open(['url' => route('property-create')]) !!}
  @endif
      <div class="form-group">
          {!! Form::label('description') !!}
          {!! Form::text('description', null, ['class' => 'form-control']) !!}
      </div>
      <div class="form-group">
          {!! Form::label('image_url') !!}
          {!! Form::text('image_url', null, ['class' => 'form-control']) !!}
      </div>
      <div class="form-group">
          <button type="submit" class="btn btn-primary">
              @if(isset($property))
                Save changes
              @else
                Create Property
              @endif
          </button>
      </div>
  {!! Form::close() !!}
</div>
