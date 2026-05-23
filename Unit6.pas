unit Unit6;

// TODO: ALSO ADD DAO GUIDS

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics,
  Controls, Forms, Dialogs, StdCtrls;

type
  TForm6 = class(TForm)
    Button1: TButton;
    Memo1: TMemo;
    procedure Button1Click(Sender: TObject);
  private
    { Private-Deklarationen }
  public
    { Public-Deklarationen }
  end;

var
  Form6: TForm6;

implementation

{$R *.dfm}

uses
  Registry;

procedure TForm6.Button1Click(Sender: TObject);

  function _GetNameOf(reg: TRegistry; keyname, s: string): string;
  var
    i,j: integer;
    tmp: string;
  begin
    result := '';
    try
      // Option A
      if reg.OpenKeyReadOnly(keyname+'\'+s) then
      begin
        result := reg.ReadString('');
        reg.CloseKey;
      end
      else
        result := '(Error)';
      // Option B
      if result = '' then
      begin
        if reg.OpenKeyReadOnly(keyname+'\'+s+'\InprocServer32') then
        begin
          result := reg.ReadString('Class');
          reg.CloseKey;
        end;
      end;
      // Option C
      if result = '' then
      begin
        for i := 1 to 99 do
        begin
          for j := 0 to 99 do
          begin
            if (result = '') and reg.OpenKeyReadOnly(keyname+'\'+s+'\'+IntToStr(i)+'.'+IntToStr(j)) then
            begin
              result := reg.ReadString('');
              reg.CloseKey;
            end;
          end;
        end;
      end;
      // Option D (not fully understood)
      if result = '' then
      begin
        if reg.OpenKeyReadOnly(keyname+'\'+s+'\AutoConvertTo') then
        begin
          tmp := reg.ReadString('');
          reg.CloseKey;
          if (tmp <> '') and (tmp <> s) then
          begin
            tmp := _GetNameOf(reg, keyname, tmp);
            if (tmp <> '') and (tmp <> '?') and (tmp <> '(Error)') then
              result := 'AutoConvert to ' + tmp;
          end;
        end;
      end;
      // Option E (not fully understood)
      if result = '' then
      begin
        if reg.OpenKeyReadOnly(keyname+'\'+s+'\TreatAs') then
        begin
          tmp := reg.ReadString('');
          reg.CloseKey;
          if (tmp <> '') and (tmp <> s) then
          begin
            tmp := _GetNameOf(reg, keyname, tmp);
            if (tmp <> '') and (tmp <> '?') and (tmp <> '(Error)') then
              result := 'TreatAs ' + tmp;
          end;
        end;
      end;
    except
      result := '(Error)';
      reg.CloseKey;
    end;
  end;

  function Test(reg: TRegistry; slOut: TStringList; keyname: string): integer;
  var
    sl: TStringList;
    s: string;
    nam: string;
    i: integer;
  begin
    result := 0;
    sl := TStringList.Create;
    try
      if reg.OpenKeyReadOnly(keyname) then
      begin
        reg.GetKeyNames(sl);
        reg.CloseKey;
        for i := 0 to sl.Count-1 do
        begin
          s := sl[i];

          if
             (
                 // {1f1f4e1a-2252-4063-84bb-eee75f8856d5}
                 (Copy(s,1,1) = '{') and
                 (Copy(s,10,1) = '-') and (Copy(s,15,1) = '-') and
                 (Copy(s,20,1) = '-') and (Copy(s,25,1) = '-') and
                 (Copy(s,38,1) = '}') and
                 ((LowerCase(Copy(s,21,1)) = 'c') or (LowerCase(Copy(s,21,1)) = 'd'))
             ) or (
                 // 1f1f4e1a-2252-4063-84bb-eee75f8856d5
                 (Copy(s,9,1) = '-') and (Copy(s,14,1) = '-') and
                 (Copy(s,19,1) = '-') and (Copy(s,24,1) = '-') and
                 ((LowerCase(Copy(s,20,1)) = 'c') or (LowerCase(Copy(s,20,1)) = 'd'))
             ) then
          begin
            if Pos('C000',UpperCase(s)) = 0 then exit; // Müll filtern

            nam := _GetNameOf(reg, keyname, s);

            if nam = '' then nam := '?';

            if (Copy(s,1,1) <> '{') and (Copy(s,38,1) <> '}') then
              s := '{' + s + '}';
            s := UpperCase(s);

            if (slOut.Values[s] = '')
               or
               (
                 ((slOut.Values[s] = '?') or (slOut.Values[s] = '(Error)')) and
                 (nam <> '?') and
                 (nam <> '(Error)')
               ) then
            begin
              slOut.Values[s] := nam;
              Inc(result);
            end;
          end;
        end;
      end;
    finally
      FreeAndNil(sl);
    end;
  end;

var
  reg: TRegistry;
  slOut: TStringList;
  newFound: Integer;
begin
  Memo1.Clear;
  slOut := TStringList.Create;
  reg := TRegistry.Create;
  try
    if FileExists('FoundGUIDs.txt') then
      slOut.LoadFromFile('FoundGUIDs.txt');
    newFound := 0;

    reg.RootKey := HKEY_CLASSES_ROOT;
    newFound := newFound + Test(reg, slOut, 'CLSID');
    newFound := newFound + Test(reg, slOut, 'Interface');
    newFound := newFound + Test(reg, slOut, 'Record');
    newFound := newFound + Test(reg, slOut, 'TypeLib');
    newFound := newFound + Test(reg, slOut, 'AppID');
    newFound := newFound + Test(reg, slOut, 'DirectShow\MediaObjects');
    newFound := newFound + Test(reg, slOut, 'Wow6432Node\DirectShow\MediaObjects');
    newFound := newFound + Test(reg, slOut, 'Component Categories');
    // Laut ChatGPT:
    newFound := newFound + Test(reg, slOut, 'ProgID');
    newFound := newFound + Test(reg, slOut, 'MIME\Database\Content Type');
    newFound := newFound + Test(reg, slOut, 'DirectShow\Filters');
    // TODO:  HKCR\Installer\..., aber dort sind die GUIDs ohne Bindestrich

    reg.RootKey := HKEY_LOCAL_MACHINE;
    newFound := newFound + Test(reg, slOut, 'SOFTWARE\Microsoft\HTMLHelp\2.0\LocalReg\CLSID');
    // Laut ChatGPT:
    newFound := newFound + Test(reg, slOut, 'SOFTWARE\Classes\CLSID');
    newFound := newFound + Test(reg, slOut, 'SOFTWARE\Classes\Interface');
    newFound := newFound + Test(reg, slOut, 'SOFTWARE\Classes\TypeLib');
    newFound := newFound + Test(reg, slOut, 'SOFTWARE\Microsoft\Active Setup\Installed Components');
    newFound := newFound + Test(reg, slOut, 'SOFTWARE\Microsoft\Windows\CurrentVersion\Shell Extensions\Approved');

    ShowMessageFmt('%d neue Legacy GUIDs gefunden', [newFound]);
    slOut.Sort;
    slOut.SaveToFile('FoundGUIDs.txt');
    memo1.Text := slOut.Text;
  finally
    FreeAndNil(reg);
    FreeAndNil(slOut);
  end;
end;

end.
