<option value=""> {{__('Select Category')}}</option>
@foreach($categories as $category)
    <option value="{{ $category->id }}">{{ $category->getTranslation('name',$default_lang) }}</option>
@endforeach
